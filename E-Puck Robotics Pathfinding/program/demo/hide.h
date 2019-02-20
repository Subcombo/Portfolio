/*Functions*/

void e_puck_hide(void); //Main hiding function

void e_puck_hide_dumb(void); // Hiding function without m-line logic

void e_puck_sleep(void); //Make robot sleep until it is discovered

void e_puck_awaken(void); //Wakes up robot after being discovered and initiates chase response

int e_puck_discovered(void); //Asks whether robot has been dicovered by another, returns 0 if flase and 1 if true

enum HitStatus is_m_line_hit_exploration(
	double s_x,
	double s_y,
	double g_x,
	double g_y
);

void saveMaxTriggered(void);

void run_bug_object_exploration(double g_x, double g_y);

void resetGlobalBug(void);

void resetTriggeredProx(void); 

void puck_light_up(void);