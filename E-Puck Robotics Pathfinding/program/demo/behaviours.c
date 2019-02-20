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

#include <stdio.h>
#include <string.h>
#include <stdlib.h>

#define TRIGGER_LEVEL 500

char gbuffer[160];

int blue_buffer[80];
int red_buffer[80];
int green_buffer[80];

int isBlueVisible, isRedVisible, isGreenVisible;

int motorSpeed = 500;
long motorSpeedCount = 0;

// Synchronous image loading into the buffer
void ggetImage(){
	e_poxxxx_launch_capture((char *)gbuffer);
    while(!e_poxxxx_is_img_ready()){};
}

// Image processing removes useless information
void gImage(){	
	long i;
	int green, red, blue;
	
	//isBlueVisible = 0;
	//isRedVisible = 0;
	isBlueVisible, isRedVisible, isGreenVisible = 0;

	for(i=0; i < 80; i++){
		// RGB turned into an integer values for comparison
		red = (gbuffer[2*i] & 0xF8);
		green = (((gbuffer[2*i] & 0x07) << 5) | ((gbuffer[2*i+1] & 0xE0) >> 3));
		blue = ((gbuffer[2*i+1] & 0x1F) << 3);
		
		if(blue > 64 && red < 64 && green < 72) {
			blue_buffer[i] = 1;
			isBlueVisible = 1;
		}else{
			blue_buffer[i] = 0;
		}

		if(red > green + 20) {  // green will be less then red when red is strong.
			red_buffer[i] = 1;
			isRedVisible = 1;
		}else{
			red_buffer[i] = 0;
		}

		if(green > red + 15){
			green_buffer[i] = 1;
			isGreenVisible = 1;
		}else{
			green_buffer[i] = 0;
		}
	}	
}

//Decide which way to turn based on the number of true pixels.
int turnDirection(int* buffer){
	int sumL = 0;
	int sumR = 0;
	int i;
	for (i=0;i<40;i++){
		sumL += buffer[i];
		sumR += buffer[i+40];
	}

	if (sumL<sumR){ 
		return 1;
	} else {
		return 0;
	}
}

//Function to deal with turning.
void blue_turn(void) {
	if (!turnDirection(blue_buffer)){
		e_set_speed_left (-500);
		e_set_speed_right(500);
	} else {
		e_set_speed_left (500);
		e_set_speed_right(-500);
	}
}


//Function to deal with turning.
void red_turn(void) {
	if (!turnDirection(red_buffer)){
		e_set_speed_left (-500);
		e_set_speed_right(500);
	} else {
		e_set_speed_left (500);
		e_set_speed_right(-500);
	}
}


//Function to deal with turning towards.
void green_turn(void) {
	if(!turnDirection(green_buffer)){
		e_set_speed_left (-500);
		e_set_speed_right(500);
	}else{
		e_set_speed_left (500);
		e_set_speed_right(-500);
	}
}


//Function to deal with turning away.
void green_turnAway(void) {
	if(!turnDirection(green_buffer)){
		e_set_speed_left (500);
		e_set_speed_right(-500);
	}else{
		e_set_speed_left (-500);
		e_set_speed_right(500);
	}
}


void delay(long interval){
	while(interval--);
}


void wiggle(void) {
	static char direction_left = 1;
	if(direction_left) {
		e_set_speed_left (200);
		e_set_speed_right(-200);
	} else {
		e_set_speed_left (-200);
		e_set_speed_right(200);
	}
	direction_left = !direction_left;
}


void forward(int speed){
	e_set_speed_left(speed);
	e_set_speed_right(speed);
}


void reverse(void){
	e_set_speed_left(-400);
	e_set_speed_right(-400);
}

void initialise(void) {
	// Set up camera 
	e_poxxxx_init_cam();
	e_poxxxx_config_cam(
		0, 						// sensor_x1
		(ARRAY_HEIGHT - 4)/2, 	// sensor_y1
		640, 					// sensor_width
		4, 						// sensor_height
		8, 						// zoom_fact_width
		4,						// zoom_fact_height
		RGB_565_MODE 			// color_mode
	);
	e_poxxxx_write_cam_registers(); 

	e_start_agendas_processing();
	e_init_prox();
}

// Will follow blue object
void loveBehaviour(void){
	initialise();
	
	while (1) {
		ggetImage();
		gImage();
		e_led_clear();
		
		int i;
		int centreWidth = 30;
		int centreValue = 0;
		for(i = (80 - centreWidth)/2; i <= (80 + centreWidth)/2; i++){
			centreValue += blue_buffer[i];
		}

		if (e_get_prox(0) + e_get_prox(7) > 300)
		{
			e_destroy_agenda(blue_turn);
			e_activate_agenda(wiggle, 4000);
			e_set_led(0, 1);
		} else if(centreValue > 3) { // If blue is in the middle then it will go forward
			e_destroy_agenda(wiggle);
			e_destroy_agenda(blue_turn);
			forward(400);
		} else {
			e_destroy_agenda(wiggle);
			e_activate_agenda(blue_turn, 500);
		}
		e_set_led(0, 0);
	}
}

// Will follow red object
void aggressionBehaviour(void){
	initialise();
	
	while (1) {
		ggetImage(); // function does..
		gImage(); // function does..
		e_led_clear(); // function does..
		
		int i; // used as a counter for loops

		// does something with camera values (black magic), for sensing?
		int centreWidth = 30; 
		int centreValue = 0;
		for(i = (80 - centreWidth)/2; i <= (80 + centreWidth)/2; i++){
			centreValue += red_buffer[i];
		}

		if(centreValue > 3) { // If is in the middle then it will go forward
			e_destroy_agenda(red_turn);
			forward(1000);
		} else if (isRedVisible) {
			e_activate_agenda(red_turn, 650);
		} else {
			e_destroy_agenda(red_turn);
			e_set_speed_left(0);
			e_set_speed_right(0);
		}

		if (e_get_prox(0) + e_get_prox(7) > 2750) //while touching something - bash it 
		{
			e_destroy_agenda(red_turn);
			forward(1000);
			reverse();
			e_set_led(0, 1);
		} else {
			e_set_led(0, 0);
		}
	}
}


//Main function of curioity, will trigger on green.
void curiousGreen(void){
	initialise();

	e_activate_agenda(green_turn, 650);

	while(1){
		ggetImage();
		gImage();
		e_led_clear();

		//Take a section of the center, this means if there is an error with one it won't effect it as a whole.
		int i = 0;
		int centreValue = 0;
		int centreWidth = 40;
		for(i = (80 - centreWidth)/2; i <= (80 + centreWidth)/2; i++){
			centreValue += green_buffer[i];
		}
		
		// Detect object within range of proximity sensors
		if ((e_get_prox(0) > 300 || e_get_prox(7) > 300)) {
			e_destroy_agenda(green_turn);
			forward(0);
			e_set_led(0,1);
			// If green is detected and within range of the proximity sensors
			if (centreValue > 3) {
					e_set_led(0,1);
					e_set_led(7,1);
					green_turnAway();
					delay(1100000);
					e_set_led(0,0);
					e_set_led(7,0);
					forward(500);
					delay(1100000);
					e_destroy_agenda(green_turn);
					forward(0);
			}
		} else {
			if(centreValue > 3) { // Detect green object, move towards
				e_set_led(1,1);
				e_destroy_agenda(green_turn);
				forward(500);			
			} else if (isGreenVisible) { // Sees green, will turn
				e_activate_agenda(green_turn, 650);
			} else { // Else do nothing
				e_set_led(0,0);
				e_activate_agenda(green_turn, 650);
			}
		}		
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

//Main function of follower
void fearGreen(void){
	initialise();

	int centreValue;
	
	forward(motorSpeed);

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

		/*if(e_get_prox(0)>800){
			//Do a 180 degree turn
			LED0 = 1;
			turnLeft();
			unsigned long i=0;
			while(i<1250000){
				i++;
			}
		}*/
		/**/
		/*if(e_get_prox(1)>800){
			LED1 = 1;
			turnLeft();
			unsigned long i=0;
			while(i<1100000){
				i++;
			}
		}

		if(e_get_prox(6)>800){
			LED6 = 1;
			turnRight();
			unsigned long i=0;
			while(i<1100000){
				i++;
			}
		}
		if(e_get_prox(7)>800){
			LED7 = 1;
			turnRight();
			unsigned long i=0;
			while(i<1250000){
				i++;
			}
		}*/

		//Check if it's time to slow down again
		if(motorSpeedCount<5 && motorSpeed>500){
			motorSpeedCount++;
		}else{
			motorSpeedCount = 0;
			motorSpeed = 500;
		}
			
		//Take a section of the center, this means if there is an error with one it won't effect it as a whole.
		int i = 35;
		centreValue = 0;
		for(i=25;i<=55; i++){
			centreValue += green_buffer[i];
		}
		if(centreValue > 3){
			e_activate_agenda(green_turnAway, 650);
			delay(600000);
			forward(motorSpeed);
			e_set_led(0,1);
			motorSpeed = 800;
		}else{
			e_destroy_agenda(green_turnAway);
			forward(motorSpeed);
			e_set_led(2,1);
		}
	}
}
