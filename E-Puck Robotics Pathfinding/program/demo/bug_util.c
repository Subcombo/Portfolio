#include "bug_util.h"

#include <motor_led/e_init_port.h>
#include <motor_led/e_epuck_ports.h>
#include <motor_led/advance_one_timer/e_motors.h>
#include <motor_led/advance_one_timer/e_agenda.h>
#include <uart/e_uart_char.h>
#include <camera/fast_2_timer/e_poxxxx.h>
#include <motor_led/advance_one_timer/e_led.h>
#include <a_d/e_ad_conv.h>
#include <a_d/e_prox.h>
#include <I2C/e_I2C_master_module.h>
#include <I2C/e_I2C_protocol.h>

#include <math.h>
#include <stdio.h>
#include <string.h>
#include <stdlib.h>

#include "trackLocation.h"
#include "hideSeekSearch.h"

#define UART_SEND(msg) e_send_uart1_char(msg,strlen(msg)); while(e_uart1_sending());
#define M_LINE_HIT_PRECISION 100.f
#define HALF_PI 1.57079632f

double last_m_hit_x = 0, last_m_hit_y = 0;
int m_hit = 0;


// Angle created by three points a, b and c,
// measured at point b
double angle_between_points(
	double a_x,
	double a_y,
	double b_x,
	double b_y,
	double c_x,
	double c_y
)
{
	// adapted from https://stackoverflow.com/a/31334882
	return atan2(a_y - b_y, a_x - b_x) - atan2(c_y - b_y, c_x - b_x);
}

// Distance between points
double distance_to_point(
	double p_x,
	double p_y,
	double q_x,
	double q_y
)
{
	double square_difference_x = (q_x - p_x) * (q_x - p_x);
    double square_difference_y = (q_y - p_y) * (q_y - p_y);
    double sum = square_difference_x + square_difference_y;
    double value = sqrt(sum);
    return value;
}

// Distance between a point (p_x, p_y)
// and the line m: (u_x, u_y) --> (v_x, v_y)
double distance_to_line(
	double u_x,
	double u_y,
	double v_x,
	double v_y,
	double p_x,
	double p_y
)
{
	double d_y = v_y - u_y;
	double d_x = v_x - u_x;
	
	// Straight line distance to m
	double dist_to_m = fabs(d_y*p_x - d_x*p_y + v_x*u_y - u_x*v_y)/sqrt(d_y*d_y + d_x*d_x);

	// Angles puv and pvu for the purpose of calculating if the intersection point
	// is ouside of the line segment
	double angle_at_u = fabs(angle_between_points(p_x, p_y, u_x, u_y, v_x, v_y));
	double angle_at_v = fabs(angle_between_points(p_x, p_y, v_x, v_y, u_x, u_y));

	// If the intersection is within the line segment, return straight line distance,
	// otherwise return distance to the respective extreme point
	if (angle_at_u <= HALF_PI && angle_at_v <= HALF_PI)
		return dist_to_m;
	else if (angle_at_u > HALF_PI)
		return distance_to_point(p_x, p_y, u_x, u_y);
	else if (angle_at_v > HALF_PI)
		return distance_to_point(p_x, p_y, v_x, v_y);
}

// Convert proximity sensor detection to centimetres
double prox_to_cm(int prox)
{
	// a and b found experimentally
	const double a = 1620.0;
	const double b = 0.76;
	return a/((double)prox) + b;
}

// Check if the line segment m: (s_x, s_y) --> (g_x, g_y) is being hit by the robot
enum HitStatus is_m_line_hit(
	double s_x,
	double s_y,
	double g_x,
	double g_y
)
{
	double pos_x = getX();
	double pos_y = getY();
	double dist_to_m = distance_to_line(s_x, s_y, g_x, g_y, pos_x, pos_y);

	// If too far away from m-line, then not hit
	if (dist_to_m > M_LINE_HIT_PRECISION)
	{
		m_hit = 0;
		return NOT_HIT;
	}

	double dist_to_last = distance_to_point(pos_x, pos_y, last_m_hit_x, last_m_hit_y);

	// If distance to last hit is lower than precision, then
	// registered the same hit again
	if (dist_to_last <= 2 * M_LINE_HIT_PRECISION)
	{
		// If we hit last_m_hit again, but there was no hit before
		// we are going in circles, and the goal is unreacheable,
		// otherwise this is the same hit as before, so ignore
		if (m_hit) return NOT_HIT;
		else return UNREACHEABLE;
	}

	// Otherwise we register the hit
	m_hit = 1;
	last_m_hit_x = pos_x;
	last_m_hit_y = pos_y;

	return HIT;
}

// Make the robot go from its current position to (g_x, g_y)
// using the bug behaviour to avoid obstacles
void run_bug_to_goal(double g_x, double g_y)
{
	double start_x = getX();
	double start_y = getY();
	enum State state = LINE_FOLLOW;

	while (1)
	{
		// If the goal has been reached, regardless of state, terminate
		if (distance_to_point(getX(), getY(), g_x, g_y) <= 100.0f) {
			char msgStr[100];
			sprintf(msgStr, "Stopping within distance of goal\r\n");
			UART_SEND(msgStr);
			stop();
			return;
		}
		
		// Finite state machine behaviour
		if (state == LINE_FOLLOW)
		{
			int hit = goToPosition(g_x, g_y);
			if (hit)  // obstacle hit while following m-line, start wall follow
			{
				state = WALL_FOLLOW;
			}
			else  // goal reached, terminate
			{
				state = FINAL;
			}
		}
		else if (state == WALL_FOLLOW)
		{
			wallFollow(1);

			double pos_x = getX();
			double pos_y = getY();
			
			double dist_to_m = distance_to_line(start_x, start_y, g_x, g_y, pos_x, pos_y);
			//char msgStr[100];
			//sprintf(msgStr, "X=%f, Y=%f, dist=%f, mh=%d\r\n", pos_x, pos_y, dist_to_m, m_hit);
			//UART_SEND(msgStr);

			enum HitStatus hit = is_m_line_hit(start_x, start_y, g_x, g_y);
			if (hit == HIT)  // m-line hit while wall following, move along it
			{
				state = LINE_FOLLOW;
			}
			else if (hit == NOT_HIT)  // otherwise carry on wall following
			{
				state = WALL_FOLLOW;
			}
			else if (hit == UNREACHEABLE)  // if goal determined to be unreacheable, give up
			{
				e_led_clear();
				e_set_led(2, 1);
				state = FINAL;
			}
		}
		else if (state == FINAL)
		{
			e_set_led(1, 1);
			m_hit = 0;
			last_m_hit_x = g_x;
			last_m_hit_y = g_y;
			return;
		}
	}
}
