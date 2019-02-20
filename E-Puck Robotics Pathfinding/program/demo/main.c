#include "p30f6014A.h"
#include <stdio.h>
#include <string.h>

#include <motor_led/e_init_port.h>
#include <motor_led/e_epuck_ports.h>
#include <motor_led/advance_one_timer/e_motors.h>
#include <motor_led/advance_one_timer/e_agenda.h>
#include <uart/e_uart_char.h>
#include <a_d/e_ad_conv.h>
#include <a_d/e_prox.h>
#include <camera/fast_2_timer/e_poxxxx.h>
#include <motor_led/advance_one_timer/e_led.h>
#include <I2C/e_I2C_master_module.h>
#include <I2C/e_I2C_protocol.h>

//#include "bug_util.h"
#include "behaviours.h"
#include "hideSeekSearch.h"
#include "trackLocation.h"
#include "hide.h"

int getselector() {
	return SELECTOR0 + 2*SELECTOR1 + 4*SELECTOR2 + 8*SELECTOR3;
}

int main()
{
	e_init_port();
	e_init_uart1();
	e_init_motors();

	e_poxxxx_init_cam();
	e_poxxxx_config_cam(0,(ARRAY_HEIGHT - 4)/2,640,4,8,4,RGB_565_MODE);
	e_poxxxx_write_cam_registers();

	e_init_prox();
	e_start_agendas_processing();

	int selector = getselector();

	switch (selector) {
	case 0:
		loveBehaviour();
		break;
	case 1:
		aggressionBehaviour();
		break;
	case 2:
		curiousGreen();
		break;
	case 3:
		fearGreen();
		break;
	case 4:
		hideSeekSearch();
		break;
	case 5:
		//e_puck_hide();
		e_puck_hide_dumb();
		break;
	case 6:
		run_bug_to_goal(3000, 3000);
		//run_bug_to_goal(0, 0);
		break;
	}

	while(1);
}
