#include <motor_led/e_init_port.h>
#include <motor_led/e_epuck_ports.h>
#include <motor_led/advance_one_timer/e_motors.h>
#include <motor_led/advance_one_timer/e_agenda.h>
#include <uart/e_uart_char.h>
#include <camera/fast_2_timer/e_poxxxx.h>
#include <motor_led/advance_one_timer/e_led.h>

#include "../../library/a_d/e_prox.h"
#include "../../library/a_d/e_prox.c"
#include "../../library/a_d/e_ad_conv.c"

#include "trackLocation.h"

#include <stdio.h>
#include <string.h>
#include <stdlib.h>

char gbuffer[160];
int gnumbuffer[80];
long isGreenVisable;

int TRIGGER_LEVEL = 500;


//custom cam picture load
void ggetImage(){
	e_poxxxx_launch_capture((char *)gbuffer);
    while(!e_poxxxx_is_img_ready()){};
}
// Image processing removes useless information
void gImage(){	
	long i;
	int green, red, vis;
	for(i=0; i<80; i++){
		//RGB turned into an integer value for comparison
		red = (gbuffer[2*i] & 0xF8);
		green = (((gbuffer[2*i] & 0x07) << 5) | ((gbuffer[2*i+1] & 0xE0) >> 3));
		//blue = ((gfbwbuffer[2*i+1] & 0x1F) << 3); we don't need blue for looking for green.
		if(green > red + 10){ //Green is usually much higher then red due the the extra bit place in RGB565
			gnumbuffer[i] = 1;
			vis +=1;
		}else{
			gnumbuffer[i] = 0;
		}
		//If Green is visable then isRedVisable turns to true
		if(vis>0){
			isGreenVisable = 1;
		}else{
			isGreenVisable = 0;
		}
	}	
}

//Decide which way to turn based on the number of true pixels.
int gturnDirection(){
	int sumL = 0;
	int sumR = 0;
	int i;
	for(i=0;i<40;i++){
		sumL += gnumbuffer[i];
		sumR += gnumbuffer[i+40];
	}
	if(sumL<sumR){ 
		return 1;
	}else{
		return 0;
	}
}
//Function to deal with turning.
void gturn(void) {
	if(!gturnDirection()){
		e_set_speed_left (500);
		e_set_speed_right(-500);
	}else{
		e_set_speed_left (-500);
		e_set_speed_right(500);
	}
}

void turnLeft(void){
	e_set_speed_left(-500);
	e_set_speed_right(500);
}

void turnRight(void){
	e_set_speed_left(500);
	e_set_speed_right(-500);
}


int motorSpeed = 500;
long motorSpeedCount = 0;

void forward(void){
	e_set_speed_left (motorSpeed);
	e_set_speed_right(motorSpeed);
}

void forwardFast(void){
	e_set_speed_left (800);
	e_set_speed_right(800);
	long delay = 0;
	while(delay<150000){
		delay+=1;
	}
}

//Main function of follower
void fearGreen(void){
	//basic set up for the camera and 
	e_poxxxx_init_cam();
	e_poxxxx_config_cam(0,(ARRAY_HEIGHT - 4)/2,640,4,8,4,RGB_565_MODE);
	e_poxxxx_write_cam_registers(); 

	e_init_prox();

	e_start_agendas_processing();
	int centreValue;
	
	//forward();
	e_set_steps_left(0);
	e_set_steps_right(0);

	while(1){
		demo();
	}

	while(1){
		ggetImage();
		gImage();
		e_led_clear();

		LED0 = 0;
		LED1 = 0;
		LED2 = 0;
		LED3 = 0;
		LED4 = 0;
		LED5 = 0;
		LED6 = 0;
		LED7 = 0;

		if(e_get_prox(0)>TRIGGER_LEVEL){
			//Do a 180 degree turn
			LED0 = 1;
			turnLeft();
			unsigned long i=0;
			while(i<1250000){
				i++;
			}
		}
		/**/
		if(e_get_prox(1)>TRIGGER_LEVEL){
			LED1 = 1;
			turnLeft();
			unsigned long i=0;
			while(i<1100000){
				i++;
			}
		}
		/*
		if(e_get_prox(2)>TRIGGER_LEVEL){
			LED2 = 1;
		}
		if(e_get_prox(3)>TRIGGER_LEVEL){
			LED3 = 1;
		}
		if(e_get_prox(4)>TRIGGER_LEVEL){
			LED4 = 1;
		}
		if(e_get_prox(5)>TRIGGER_LEVEL){
			LED5 = 1;
		}*/
		if(e_get_prox(6)>TRIGGER_LEVEL){
			LED6 = 1;
			turnRight();
			unsigned long i=0;
			while(i<1100000){
				i++;
			}
		}
		if(e_get_prox(7)>TRIGGER_LEVEL){
			LED7 = 1;
			turnRight();
			unsigned long i=0;
			while(i<1250000){
				i++;
			}
		}

		//Check if it's time to slow down again
		if(motorSpeedCount<5 && motorSpeed>500){
			motorSpeedCount++;
		}else{
			motorSpeedCount = 0;
			motorSpeed = 500;
		}
			
		//Take a section of the center, this means if there is an error with one it won't effect it as a whole.
		//centreValue = gnumbuffer[38] + gnumbuffer[39] + gnumbuffer[40] + gnumbuffer[41] + gnumbuffer[42] + gnumbuffer[43]; // removes stray 
		int i = 35;
		centreValue = 0;
		for(i=25;i<=55; i++){
			centreValue += gnumbuffer[i];
		}
		if(centreValue > 3){
			e_activate_agenda(gturn, 650);
			long delay = 0;
			while(delay<300000){
				delay+=1;
			}
			forward();
			e_set_led(0,1);
			motorSpeed = 800;
		}else{
			e_destroy_agenda(gturn);
			forward();
			e_set_led(2,1);
		}
	}
}

