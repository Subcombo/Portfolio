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


#include <math.h>
#include <stdio.h>
#include <string.h>
#include <stdlib.h>
#define PI 3.14159265359
//One step is this many degrees when applied to both wheels
#define DEG2STEP 3.555555555555556
#define STEP2DEG 1/DEG2STEP

double posX = 0;
double posY = 0;
double angle = 0;

//Degrees * DEG2RAD = Rad
double DEG2RAD = PI / 180;
double RAD2DEG = 180 / PI;

int STEPS360 = 1332;

int PROX_LEVEL = 600;

double degreeToRad(double degree){
	return degree*DEG2RAD;
}

double radToDegree(double rad){
	return rad*RAD2DEG;
}

int moveForward(int steps, int speed){
	int hit = 0;
	e_set_steps_left(0);
	e_set_steps_right(0);

	e_set_speed_left(speed);
	e_set_speed_right(speed);
	while(e_get_steps_left() < steps){
		if(e_get_prox(0)>PROX_LEVEL || e_get_prox(7)>PROX_LEVEL){
			hit = 1;
			break;
		}
		asm("NOP");
	}

	//Caclulate X&Y
	//angleRad = angle*DEG2RAD
	//X = Steps*Sin(angleRad)
	//Y = Steps*Cos(angleRad)
	double angleRad = degreeToRad(angle);
	double xChange = e_get_steps_left()*sin(angleRad);
	double yChange = e_get_steps_left()*cos(angleRad);

	posX += xChange;
	posY += yChange;

	e_set_speed_left(0);
	e_set_speed_right(0);

	return hit;
}

void moveForwardDumb(int steps, int speed){
	e_set_steps_left(0);
	e_set_steps_right(0);

	e_set_speed_left(speed);
	e_set_speed_right(speed);
	while(e_get_steps_left() < steps){
		asm("NOP");
	}

	//Caclulate X&Y
	//angleRad = angle*DEG2RAD
	//X = Steps*Sin(angleRad)
	//Y = Steps*Cos(angleRad)
	double angleRad = degreeToRad(angle);
	double xChange = e_get_steps_left()*sin(angleRad);
	double yChange = e_get_steps_left()*cos(angleRad);

	posX += xChange;
	posY += yChange;

	e_set_speed_left(0);
	e_set_speed_right(0);
}

void moveBackward(int steps, int speed){
	e_set_steps_left(0);
	e_set_steps_right(0);

	e_set_speed_left(-speed);
	e_set_speed_right(-speed);
	while(e_get_steps_left() > -steps){
		asm("NOP");
	}

	//Caclulate X&Y
	//angleRad = angle*DEG2RAD
	//X = Steps*Sin(angleRad)
	//Y = Steps*Cos(angleRad)
	double angleRad = degreeToRad(angle);
	double xChange = steps*sin(angleRad);
	double yChange = steps*cos(angleRad);

	posX -= xChange;
	posY -= yChange;

	e_set_speed_left(0);
	e_set_speed_right(0);
}

void turnCW(int degrees){
	angle+=degrees;

	//char angleStr[100];
	//sprintf(angleStr,"Turning To: %e\r\n",angle);
	//e_send_uart1_char(angleStr,strlen(angleStr));

	int steps = (int)(degrees * DEG2STEP);
	e_set_steps_left(0);
	e_set_steps_right(0);

	e_set_speed_left(500);
	e_set_speed_right(-500);
	
	while(e_get_steps_left() < steps){
		//Do nothing
	}

	e_set_speed_left(0);
	e_set_speed_right(0);
	
}

void turnACW(int degrees){
	angle-=degrees;

	//char angleStr[100];
	//sprintf(angleStr,"Turning To: %e\r\n",angle);
	//e_send_uart1_char(angleStr, strlen(angleStr));

	int steps = (int)(degrees * DEG2STEP);
	e_set_steps_left(0);
	e_set_steps_right(0);

	e_set_speed_left(-500);
	e_set_speed_right(500);
	
	while(e_get_steps_right() < steps){
		//Do nothing
	}

	e_set_speed_left(0);
	e_set_speed_right(0);
}

void returnToStart(){
	int steps = sqrt((posX*posX) + (posY*posY));
	double beta = 0;
	if(posY == 0){
		beta = 0.5*PI;
	}else{
		beta = atan(posX/posY);
	}
	beta = radToDegree(beta);
	
	angle = fmod(angle,360);
	
	double angleTurn = 360-(180-beta)-angle;

	if(posY<0){
		angleTurn+=180;
	}
	
	if(angleTurn > 180){
		turnACW(360-angleTurn);
	}else{
		turnCW(angleTurn);
	}

	moveForward(steps, 500);

}

int goToPosition(newX, newY){
	float xChange = newX - posX;
	float yChange = newY - posY;
	
	int steps = sqrt(pow((xChange),2)+pow(yChange,2));
	float beta = 0;
	if(yChange == 0){
		beta = 0.5*PI;
	}else{
		beta = atan(xChange/yChange);
	}
	beta = radToDegree(beta);
	int angleTurn = 0;

	if(yChange >= 0){
		angleTurn = beta - angle;
	}else{
		angleTurn = (180 + beta) - angle;
	}

	if(angleTurn < 0){angleTurn+=360;};

	
	if(angleTurn > 180){
		turnACW(360-angleTurn);
	}else{
		turnCW(angleTurn);
	}

	return moveForward(steps, 500);

}

void turnToStart(){
	angle = fmod(angle, 360);

	if(angle<0){angle+=360;};

	if(angle<180){
		turnACW(angle);
	}else{
		turnCW(360-angle);
	}
}

void initPositionTracking(){
	e_set_steps_left(0);
	e_set_steps_right(0);
}

void stepsToDegree(){
	int stepsLeft = e_get_steps_left();
	int stepsRight = e_get_steps_right();

	int angleChange = (int)((stepsLeft - stepsRight) * DEG2STEP/2);
	angle+=angleChange;
	angle = fmod(angle, 360);
}

void updatePosition(){
	int stepsLeft = e_get_steps_left();
	int stepsRight = e_get_steps_right();

	int stepsAVG = (stepsLeft + stepsRight)/2;
	
	//Get Effective distance since last time
	//Update position X&Y using current angle
	double angleRad = degreeToRad(angle);
	double xChange = stepsAVG*sin(angleRad);
	double yChange = stepsAVG*cos(angleRad);

	posX += xChange;
	posY += yChange;

	//Update new robot angle
	stepsToDegree();

	e_set_steps_left(0);
	e_set_steps_right(0);
}

void stop(){
	e_set_speed_left(0);
	e_set_speed_right(0);
}

double getX(){
	return posX;
}

double getY(){
	return posY;
}

void demo(){
	e_init_port();
	e_init_uart1();
	e_init_motors();

	e_poxxxx_init_cam();
	e_poxxxx_config_cam(0,(ARRAY_HEIGHT - 4)/2,640,4,8,4,RGB_565_MODE);
	e_poxxxx_write_cam_registers(); 

	e_init_prox();
	e_start_agendas_processing();

	//initPositionTracking();

	/*e_set_speed_left(500);
	e_set_speed_right(500);*/

	goToPosition(0,-1000);
	goToPosition(1000, 0);
	goToPosition(-1000, 0);
	goToPosition(0,1000);
	returnToStart();
	turnToStart();
	//goToPosition(-1000, 2000);
	//goToPosition(500, 1500);
	//goToPosition(0,0);
	//turnToStart();


	/*moveForward(1000,500);
	turnCW(60);
	moveForward(2500,500);
	turnCW(90);
	moveForward(1000,500);
	turnCW(30);
	moveForward(1000,500);
	returnToStart();
	turnToStart();*/
	while(1);
}
