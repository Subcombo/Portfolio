#include <motor_led/e_epuck_ports.h>
#include <motor_led/e_init_port.h>
#include "../../library/a_d/e_prox.h"
//#include "../../library/a_d/e_prox.c"
//#include "../../library/a_d/e_ad_conv.c"

int TRIGGER_LEVEL = 500;

void avoidProximity(){
	e_init_port();
	e_init_prox();

	while(1){
		LED0 = 0;
		LED1 = 0;
		LED2 = 0;
		LED3 = 0;
		LED4 = 0;
		LED5 = 0;
		LED6 = 0;
		LED7 = 0;

		if(e_get_prox(0)>TRIGGER_LEVEL){
			LED0 = 1;
		}
		if(e_get_prox(1)>TRIGGER_LEVEL){
			LED1 = 1;
		}
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
		}
		if(e_get_prox(6)>TRIGGER_LEVEL){
			LED6 = 1;
		}
		if(e_get_prox(7)>TRIGGER_LEVEL){
			LED7 = 1;
		}
	}
}