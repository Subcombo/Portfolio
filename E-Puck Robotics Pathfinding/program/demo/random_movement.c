/* ! \File
* \brief Allows the movement of the e_puck in a random direction
*
* This code file will havee a few functions in order to generate the movement
*   # One function will generate a random movement
*   # Another will work out the angle theta of the new direction
*   # The last will convert the angle theta in to a rotation of the epuck
*
* \author Joseph Stevens
*/

#include <p30f6014A.h>
#include <stdlib.h>
#include <math.h>
#include <time.h>

#include "random_movement.h"
#include <motor_led/e_init_port.h>
#include <motor_led/e_epuck_ports.h>
#include <motor_led/advance_one_timer/e_motors.h>
#include <a_d/e_ad_conv.h>
#include <a_d/e_prox.h>
#include "randdir.h"

#define steps_2pi 1300
#define PI  3.1415

/* Functions*/

/* Gets direction as angle theta between epuck and object
 * and works out the number of steps needed to make a counter clockwise
 * rotation then sets the left motor to rotate the epuck
 */
void rd_epuck_move(double theta)
{
	int turn_steps;

	//stop motors temporarily
	e_set_speed_left(0);
	e_set_speed_right(0);
	
	//set steps to zero
	e_set_steps_left(0);
	e_set_steps_right(0);

	turn_steps = (int)(steps_2pi*(theta / (2 * PI))); //Work out the number of steps needed for rotation
	
	int time_needed = 500 * turn_steps; //Work out time is seconds needed for rotation

	e_set_speed_left(-500); //Start motors at half speed in counter clockwise direction
	e_set_speed_right(0);

	int Max_i = time_needed + 300000; //Loops needed to run function
	long i;
	for(i = 0; i < Max_i; i++){
		asm("nop");
	}
	
}

/* Gets distance as vector (x, y) between epuck and object and 
* retrieves an angle to call rd_epuck_move(theta) for the robot to rotate.
* The epuck is then asked to continue moving in a straight line post rotation
*/
void get_randdir()
{
	int x_dist_to_object = 200; //Distance from object to epuck in x direction
	int y_dist_to_object = 200; //Distance from object to epuck in y direction

	double theta = get_theta(x_dist_to_object, y_dist_to_object); //Angle of rotation for object to face object
	
	
	//Find a random direction of movement
	int x_new = x_dist_to_object - randdir();
	int y_new = x_dist_to_object - randdir();

	double phi = get_theta(x_new, y_new); //Get new angle of direction
	rd_epuck_move(phi); //Move object in random direction

	e_set_speed_left(-500);
	e_set_speed_right(-500);
}

//Works out value of angle between vector and object using eq.#
// theta = arctan(x/y)
double get_theta(int x, int y)
{
	double r = x / y;
	double theta = atan(r);
	return theta;
}