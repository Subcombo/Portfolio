#ifndef _BUGUTIL
#define _BUGUTIL

enum State {
	LINE_FOLLOW,
	WALL_FOLLOW,
	FINAL
};

enum HitStatus {
	NOT_HIT = 0,
	HIT = 1,
	UNREACHEABLE = 2
};

double angle_between_points(
	double a_x,
	double a_y,
	double b_x,
	double b_y,
	double c_x,
	double c_y
);

double distance_to_line(
	double u_x,
	double u_y,
	double v_x,
	double v_y,
	double p_x,
	double p_y
);

double distance_to_point(
	double p_x,
	double p_y,
	double q_x,
	double q_y
);

double prox_to_cm(int prox);

enum HitStatus is_m_line_hit(
	double s_x,
	double s_y,
	double g_x,
	double g_y
);

void run_bug_to_goal(double g_x, double g_y);

#endif
