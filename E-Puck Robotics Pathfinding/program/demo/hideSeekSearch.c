#include "trackLocation.h"
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

//Included for the camera image recognition
//#include "behaviours.h"

#define TRIGGER_LEVEL_CLOSE 1000
#define TRIGGER_LEVEL_FRONT_TOO_CLOSE 600
#define TRIGGER_LEVEL_FAR 600
#define TRIGGER_LEVEL_AWAY 400
#define MIN(x, y)  ((x) > (y) ? (y) : (x))
#define MIN3(x, y, z) (MIN(x, y), (z))

char gbuffer2[160];
int green_buffer2[80];

void ggetImage2(){
	e_poxxxx_launch_capture((char *)gbuffer2);
    while(!e_poxxxx_is_img_ready()){};
}

// Image processing removes useless information
void gImage2(){	
	long i;
	int green, red, blue;
	/*
		LOOKS FOR RED EVEN THOUGH VARIABLES ARE CALLED GREEN
	*/
	
	//isBlueVisible = 0;
	//isRedVisible = 0;
	int isGreenVisible = 0;

	for(i=0; i < 80; i++){
		// RGB turned into an integer values for comparison
		red = (gbuffer2[2*i] & 0xF8);
		green = (((gbuffer2[2*i] & 0x07) << 5) | ((gbuffer2[2*i+1] & 0xE0) >> 3));

		if(red > green + 15){
			green_buffer2[i] = 1;
			isGreenVisible = 1;
		}else{
			green_buffer2[i] = 0;
		}
	}	
}

/*
Wall Follow Algorithm
2 Sensors - (F)orwards & (Right)
FR
00 - Robot is driving away from wall, face right
10 - Robot is heading towards wall, turn left
01 - Robot is following wall
11 - Robot is at a corner, turn left
*/

void wallFollow(int loops){
	int loopCount = 0;
	
	//While each wheel has done less the the required steps keep looping
	while(loopCount < loops){
		//Set motorspeeds based on sensors
		int rS = e_get_prox(2);
		int fS = e_get_prox(0);
		
		e_led_clear();
		
		if(fS > TRIGGER_LEVEL_FRONT_TOO_CLOSE)
		{
			e_set_led(5, 1);
			turnACW(30);
		}
		else if(rS < TRIGGER_LEVEL_FAR)
		{
			e_set_led(1, 1);
			turnCW(10);
		
			rS = e_get_prox(2);
			while(rS < TRIGGER_LEVEL_AWAY)
			{
				e_set_led(2, 1);
				moveForwardDumb(50,300);
				turnACW(30);
				rS = e_get_prox(2);
			}
		}
		else if(rS > TRIGGER_LEVEL_CLOSE)
		{
			e_set_led(3, 1);
			turnACW(10);
		}
		else if(rS > TRIGGER_LEVEL_FAR && rS < TRIGGER_LEVEL_CLOSE)
		{
			e_set_led(4, 1);
			moveForward(50,300);
		}

		loopCount++;
	}
}

void hideSeekSearch(){
	//Int to keep track of how many robots we have found
	int foundState = 0;

	e_init_port();
	e_init_uart1();
	e_init_motors();

	e_poxxxx_init_cam();
	e_poxxxx_config_cam(0,(ARRAY_HEIGHT - 4)/2,640,4,8,4,RGB_565_MODE);
	e_poxxxx_write_cam_registers(); 

	e_init_prox();
	e_start_agendas_processing();

	long delayCount = 0;
	while(delayCount<10000000 && 0){
		delayCount+=1;
	}

	goToPosition(200,200);
	moveForward(10000,500);

	//int hit = goToPosition(0,2000);
	//while(moveForward(500,500) != 1){};
	//Will stop when it detects something ahead.
	//In this case the wall of the obstacle
	//turnACW(45);

	while(foundState == 0){
		//Wall follow for a bit
		//Assume wall always on right
		wallFollow(10);
		stop();

		//Turn clockwise 90, check camera
		/*turnCW(90);
		ggetImage2();
		gImage2();
		int i=0;
		int centreValue = 0;
		for(i=25; i<=55; i++){
			centreValue += green_buffer2[i];
		}

		if(centreValue > 3){
			moveForward(1000,500);
			foundState = 1;
			moveBackward(1000,500);
			break;
		}

		//Turn anticlockwise 90, check camera
		turnACW(90);*/
		ggetImage2();
		gImage2();
		int i=0;
		int centreValue = 0;
		for(i=25; i<=55; i++){
			centreValue += green_buffer2[i];
		}

		if(centreValue > 3){
			moveForward(1000,500);
			foundState = 1;
			moveBackward(1000,500);
			break;
		}

		//Check camera for goal
		
		
		
	}

	LED7 = 1;
	//Wait 10 seconds
	delayCount = 0;
	while(delayCount<16000000){
		delayCount++;
	}

	//Move to object 2
	//Bug algortihm half way
	turnACW(90);
	moveForward(10000,500);
	
	while(foundState == 1){
		//Wall follow for a bit
		//Assume wall always on right
		wallFollow(20);
		stop();

		//Check camera for obstacle
		ggetImage2();
		gImage2();
		
		//Turn clockwise 90, check camera
		turnCW(90);
		ggetImage2();
		gImage2();
		int i=0;
		int centreValue = 0;
		for(i=25; i<=55; i++){
			centreValue += green_buffer2[i];
		}

		if(centreValue > 3){
			moveForward(1000,500);
			foundState = 1;
			moveBackward(1000,500);
			break;
		}

		//Turn anticlockwise 90, check camera
		turnACW(90);
		ggetImage2();
		gImage2();
		i=0;
		centreValue = 0;
		for(i=25; i<=55; i++){
			centreValue += green_buffer2[i];
		}

		if(centreValue > 3){
			moveForward(1000,500);
			foundState = 2;
			moveBackward(1000,500);
			break;
		}
		
		
	}

	run_bug_to_goal(0,0);
	

	//Found first puck, drive towards
	

	//Check for target robot
	
	//Move round obstacle a bit more (wall follow)

}
