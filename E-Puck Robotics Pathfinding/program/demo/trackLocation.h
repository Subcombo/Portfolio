#ifndef _TRACKLOCATION
#define _TRACKLOCATION

double degreeToRad(double degree);

double radToDegree(double rad);

int moveForward(int steps, int speed);
void moveForwardDumb(int steps, int speed);

void moveBackward(int steps, int speed);

void turnCW(int degrees);

void turnACW(int degrees);

void turnToStart(void);

int goToPosition(int newX, int newY);

void initPositionTracking(void);

void stepsToDegree(void);

void demo(void);

void updatePosition(void);

double getX(void);

double getY(void);

void stop(void);

void demo(void);

#endif
