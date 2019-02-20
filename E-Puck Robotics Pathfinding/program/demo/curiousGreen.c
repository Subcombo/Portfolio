#include <motor_led/e_init_port.h>
#include <motor_led/e_epuck_ports.h>
#include <motor_led/advance_one_timer/e_motors.h>
#include <motor_led/advance_one_timer/e_agenda.h>
#include <uart/e_uart_char.h>
#include <camera/fast_2_timer/e_poxxxx.h>
#include <motor_led/advance_one_timer/e_led.h>
#include <a_d/e_prox.h>

#include <stdio.h>
#include <string.h>
#include <stdlib.h>

char gbuffer[160];
int gnumbuffer[80];
long isGreenVisable;


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
		if(green > red + 55){
			gnumbuffer[i] = 1;
			vis +=1;
		}else{
			gnumbuffer[i] = 0;
		}

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

//Function to deal with turning towards.
void gturn(void) {
	if(!gturnDirection()){
		e_set_speed_left (-500);
		e_set_speed_right(500);
	}else{
		e_set_speed_left (500);
		e_set_speed_right(-500);
	}
}

//Function to deal with turning away.
void gturnAway(void) {
	if(!gturnDirection()){
		e_set_speed_left (500);
		e_set_speed_right(-500);
	}else{
		e_set_speed_left (-500);
		e_set_speed_right(500);
	}
}

//Function to deal with moving forward.
void forward(void){
	e_set_speed_left (500);
	e_set_speed_right(500);
}

//Function to deal with stopping movement.
void stop(void) {
	e_destroy_agenda(gturn);
	e_set_speed_left(0);
	e_set_speed_right(0);
}

//Function to create a delay.
void delay(void){
	long counter = 1100000;
	long i;
	for(i=0;i<counter;i++) {
		asm("nop");
	}
}

//Main function of curioity, will trigger on green.
void curiousGreen(void){
	//Set up camera
	e_poxxxx_init_cam();
	e_poxxxx_config_cam(0,(ARRAY_HEIGHT - 4)/2,640,4,8,4,RGB_565_MODE);
	e_poxxxx_write_cam_registers(); 
	e_init_prox();

	e_start_agendas_processing();

	while(1){
		ggetImage();
		gImage();
		e_led_clear();

		//Take a section of the center, this means if there is an error with one it won't effect it as a whole.
		int i = 0;
		int centreValue = 0;
		int centreWidth = 40;
		for(i = (80 - centreWidth)/2; i <= (80 + centreWidth)/2; i++){
			centreValue += gnumbuffer[i];
		}
		
		// Detect object within range of proximity sensors
		if ((e_get_prox(0) > 200 || e_get_prox(7) > 200)) {
			stop();
			e_set_led(0,1);
			// If green is detected and within range of the proximity sensors
			if (centreValue > 3) {
					e_set_led(1,1);
					e_set_led(2,1);
					e_set_led(3,1);
					gturnAway();
					delay();
					e_set_led(1,0);
					e_set_led(2,0);
					e_set_led(3,0);
					forward();
					delay();
					stop();
			}
		} else {
			if(centreValue > 3) { // Detect green object, move towards
				e_set_led(1,1);
				e_destroy_agenda(gturn);
				forward();			
			} else if (isGreenVisable) { // Sees green, will turn
				e_activate_agenda(gturn, 650);
			} else { // Else do nothing
				e_set_led(0,0);
				e_destroy_agenda(gturn);
			}
		}		
	}	
}

