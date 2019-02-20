#include <p30f6014A.h>
#include <stdlib.h>
#include <math.h>
#include <time.h>

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

#include "behaviours.h"
#include "random_movement.h"
#include "randdir.h"
#include "hide.h"
#include "get_prox.h"
#include "trackLocation.h"
#include "hideSeekSearch.h"
#include "bug_util.h"

#define steps_2pi 1300
#define PI  3.14159265359

#define TRIGGER_LEVEL 500
#define TRIGGER_LEVEL_FRONT 800

int hidei = 0;
double piover4 = PI/4;

int theta = 45; //=45 degrees

// Object positions and proximity triggered values
int positionCounter = 0;
int maxTriggeredProx = 0;
double maxTriggeredX = 0;
double maxTriggeredY = 0;

int objectPositions[2][2] = {
	{2000, 2000},
	{0, 4000}
};

//////////////////////////////////////////////
/* 				Bug behaviour			   	*/
//////////////////////////////////////////////


#define UART_SEND(msg) e_send_uart1_char(msg,strlen(msg)); while(e_uart1_sending());
#define M_LINE_HIT_PRECISION 100.f
#define HALF_PI 1.57079632f

double last_m_hit_line_x = 0, last_m_hit_line_y = 0;
int m_hit_line = 0;
int m_hit_line_count = 0;

// Bug 1 behaviour m_line_hit check
enum HitStatus is_m_line_hit_exploration(
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
		m_hit_line = 0;
		return NOT_HIT;
	}

	double dist_to_last = distance_to_point(pos_x, pos_y, last_m_hit_line_x, last_m_hit_line_y);

	// If distance to last hit is lower than precision, then
	// registered the same hit again
	if (dist_to_last <= 2 * M_LINE_HIT_PRECISION)
	{
		// If we hit last_m_hit_line again, but there was no hit before
		// we are going in circles, and the goal is unreacheable,
		// otherwise this is the same hit as before, so ignore

		// now based on count of times hit so it explores full obstacle
		if (m_hit_line_count <= 1) return NOT_HIT;
		else return UNREACHEABLE;
	}

	// Otherwise we register the hit
	m_hit_line = 1;
	m_hit_line_count += 1;
	last_m_hit_line_x = pos_x;
	last_m_hit_line_y = pos_y;

	return HIT;
}

// Save the number of triggered proximity sensors
void saveMaxTriggered(void) {
	// Update maximum triggered sensors
	int k = 0;
	k = triggered_proxy();

	if (k > maxTriggeredProx) {
		maxTriggeredProx = k;
		maxTriggeredX = getX();
		maxTriggeredY = getY();
	}
}

// Bug behaviour for exploring an object fully
void run_bug_object_exploration(double g_x, double g_y)
{
	double start_x = getX();
	double start_y = getY();
	enum State state = LINE_FOLLOW;

	while (1)
	{
		saveMaxTriggered();

		if (state == LINE_FOLLOW)
		{
			int hit = goToPosition(g_x, g_y);
			if (hit)
			{
				saveMaxTriggered();
				last_m_hit_line_x = getX();
				last_m_hit_line_y = getY();

				state = WALL_FOLLOW;
			}
			else
			{
				state = FINAL;
			}
		}
		else if (state == WALL_FOLLOW)
		{
			saveMaxTriggered();
			wallFollow(1);
			saveMaxTriggered();

			double pos_x = getX();
			double pos_y = getY();

			double dist_to_m = distance_to_line(start_x, start_y, g_x, g_y, pos_x, pos_y);

			enum HitStatus hit = is_m_line_hit_exploration(start_x, start_y, g_x, g_y);
			if (hit == HIT)
			{
				if(m_hit_line_count >= 2) {
					return;
				} else {
					state = WALL_FOLLOW;
				}
			}
			else if (hit == NOT_HIT)
			{
				state = WALL_FOLLOW;
			}
			else if (hit == UNREACHEABLE)
			{
				e_led_clear();
				e_set_led(2, 1);
				state = FINAL;
			}
		}
		else if (state == FINAL)
		{
			e_set_led(1, 1);
			m_hit_line = 0;
			last_m_hit_line_x = g_x;
			last_m_hit_line_y = g_y;
			return;
		}
	}
}

// Resets the bug behaviour trackers between objects
void resetGlobalBug(void) {
	last_m_hit_line_x = getX();
	last_m_hit_line_y = getY();

	m_hit_line = 0;
	m_hit_line_count = 0;
}

void resetTriggeredProx(void) { 
	maxTriggeredProx = 0;
	maxTriggeredX = getX();
	maxTriggeredY = getY();
}

//////////////////////////////////////////////
/* 				Main code 				   	*/
//////////////////////////////////////////////

// Main hide code with m-line logic
void e_puck_hide(void)
{ /*Epuck starts motors seeking for most "hidable" object, i.e the object that
   *triggers the most of its proxy sensors subject to > 2 and then uses that spot to execute
   *e_puck_sleep().*/
	resetGlobalBug();
	resetTriggeredProx();

	e_init_prox();

	// Size of multidimensional array
	int row = sizeof(objectPositions) / sizeof(objectPositions[0]);
	
	while (positionCounter < row) {
		// Explore object
		if (positionCounter > 0 ) {
			goToPosition(objectPositions[positionCounter][0]*0.75, objectPositions[positionCounter][1]*0.75);
		}
		
		// explore the object
		run_bug_object_exploration(objectPositions[positionCounter][0], objectPositions[positionCounter][1]);
	
		e_led_clear();
		stop();

		// wall follow to goal position
		int foundState = 0;
		while (foundState < 1) {
			wallFollow(1);
			double dist = distance_to_point(getX(), getY(), maxTriggeredX, maxTriggeredY);
			if (dist < 300.0f) {
				// if within precision allowance - found goal
				foundState = 1;
			}
		}	
	
		e_led_clear();
		stop();
		
		resetGlobalBug();	
		resetTriggeredProx();
		positionCounter += 1;
		e_puck_sleep();
	}

	// Return home
	run_bug_to_goal(0, 0);
}

// light up all leds
void puck_light_up(void) {
	e_led_clear();
	e_set_led(0,1);
	e_set_led(1,1);
	e_set_led(2,1);
	e_set_led(3,1);
	e_set_led(4,1);
	e_set_led(5,1);
	e_set_led(6,1);
	e_set_led(7,1);
}	

// Main hide code - without m-line logic
void e_puck_hide_dumb(void) {
	resetGlobalBug();
	resetTriggeredProx();

	e_init_prox();

	// Size of multidimensional array
	int row = sizeof(objectPositions) / sizeof(objectPositions[0]);
	int i = 0;
	for (i = 0; i< row; i++) {
		int hiding = 0;
		int hit = goToPosition(objectPositions[positionCounter][0], objectPositions[positionCounter][1]);
		
		while(hiding == 0) {	
			// if an object is hit, begin wall following
			if (hit) {
				wallFollow(1);
				// if a suitable hiding spot has been found, stop
				if (triggered_proxy() > 4) {
					hiding = 1;
					positionCounter = positionCounter + 1;
					
					// wait to be discovered
					e_led_clear();
					e_puck_sleep();
					e_led_clear();

					long i;
					for(i = 0; i < 300000; i++){
						asm("nop");
					}
				}
			}
		}
	}
	
	e_led_clear();	
	// Return home
	run_bug_to_goal(0,0);
}

/*Once robot has found a suitable hiding spot via sensors, the robot turns off its motors effecively putting
    *it to sleep until it is detected by another epuck*/
void e_puck_sleep(void)
{
	long i;
	for(i = 0; i < 100000; i++){
		asm("nop");
	}

	maxTriggeredProx = triggered_proxy();

	int b = 0;
	while(b < 1){
		b = e_puck_discovered(); //Has epuck been discovered?
	}
	if(b == 1){ //If so,
		e_puck_awaken(); //Wake up
	}

}

//Test to ensure whether the epuck has been discoved, i.e an increase in triggered proxy sensors
int e_puck_discovered(void)
{
	int z = 0;

	if (triggered_proxy() > maxTriggeredProx) {
		hidei = hidei + 1;
		z = 1;
	}

	return z;
}

/* Restarts epuck executing function depending on the mode indicated by hidei
	*case hidei = 1: repeat e_puck_hide()
	*case hidei = 2: return to start (turnToStart()) had set hidei to zero
*/
void e_puck_awaken()
{
	if(hidei > 1)
	{		
		e_set_led(5, 1);
		e_set_led(3, 0);
		long i;
		for(i = 0; i < 100000; i++){
			asm("nop");
		}
		hidei=0;
		stop();
	}
	else
	{
		e_set_led(3, 1);
		long i;
		for(i = 0; i < 100000; i++){
			asm("nop");
		}
	}
}
