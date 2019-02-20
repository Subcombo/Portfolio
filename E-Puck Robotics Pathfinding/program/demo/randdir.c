#include "randdir.h"

/*
* brief/ Generates random direction inorder for the epuck to turn towards and travel in
*  if value is in interval (-1, 1) the function will be recalled
* author/ Joseph Stevens
*/

#include <math.h>


int randdir() 
{
	int r = rand() % 1000; //Generate random integer in interval [-1000, 1000]

	if((r > -1) && (r < 1)) //if value lies in interval (-1, 1)
	{
		randdir(); //recall function
	}
	else
	{
		return r;
	}
}