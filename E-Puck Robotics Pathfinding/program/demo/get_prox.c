

#include <p30f6014A.h>
#include <stdlib.h>
#include <math.h>
#include <time.h>


#include <motor_led/e_epuck_ports.h>
#include <motor_led/e_init_port.h>
#include <motor_led/advance_one_timer/e_motors.h>
#include <a_d/e_prox.h>
#include "random_movement.h"
#include "randdir.h"
#include "get_prox.h"


#define steps_2pi 1300
#define PI  3.1415

int rtrn_proxy(void)
{//Returns number of proxy sensors triggered after an object has been discovered.
	int i;
	int k = 0; //counter for amount of proxy sensors triggered
	int value;

	for(i=0; i < 8; i++)
	{
		value = e_get_prox(i);
		if(value > 300) //If obsticle is detected by sensor
		{
			k++; //Increment counter
		}
	}

	if(k > 2)
	{
		return k;
	}
	else
	{
		return 0;
	}
}

int triggered_proxy(void)
{/*Once object has been found, the amount of triggered prox sensors are recorded in array, prox_trix and then
  *works out if it is the highest amount*/
	int i;
	int maxk = 0;
	int prox_trig[11] = {};

// Behaviour takes too long for the program to keep up
//	for(i=0; i < 10; i++)
//	{
//		prox_trig[i] = rtrn_proxy();
//		if((i>0) && (prox_trig[i] > prox_trig[i-1]))
//		{
//			maxk = prox_trig[i];
//		}
//	}
//	return maxk;
	return rtrn_proxy();
}
