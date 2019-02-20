

/*
* \brief Header file for the functions needed for the random movement of the epuck
* Note: the Epuck can only move within a straight line
* referal to the e_puck_motor will be needed
*
* \author Joseph Stevens
*/

/* Functions*/


void get_randdir(); //generate a random movement

double get_theta(int x, int y); //Calculate angle of rotation

void rd_epuck_move(double theta);