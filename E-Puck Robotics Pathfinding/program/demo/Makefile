# MPLAB IDE generated this makefile for use with GNU make.
# Project: Demo.mcp
# Date: Tue Dec 04 15:19:09 2018

AS = pic30-as.exe
CC = pic30-gcc.exe
LD = pic30-ld.exe
AR = pic30-ar.exe
HX = pic30-bin2hex.exe
RM = rm

Demo.hex : Demo.cof
	$(HX) "Demo.cof"

Demo.cof : obj/e_init_port.o obj/e_init_uart1.o obj/e_uart1_rx_char.o obj/e_uart1_tx_char.o obj/e_agenda.o obj/e_motors.o obj/main.o obj/e_common.o obj/e_interrupt.o obj/e_po3030k_registers.o obj/e_po6030k_registers.o obj/e_timers.o obj/e_calc_po3030k.o obj/e_calc_po6030k.o obj/e_calc_po8030d.o obj/e_po8030d_registers.o obj/e_lsm330.o obj/e_led.o obj/e_I2C_master_module.o obj/e_I2C_protocol.o obj/e_accelerometer.o obj/e_ad_conv.o obj/e_prox.o obj/behaviours.o obj/avoidProximity.o obj/hideSeekSearch.o obj/randdir.o obj/random_movement.o obj/trackLocation.o obj/bug_util.o
	$(CC) -mcpu=30F6014A "obj\e_init_port.o" "obj\e_init_uart1.o" "obj\e_uart1_rx_char.o" "obj\e_uart1_tx_char.o" "obj\e_agenda.o" "obj\e_motors.o" "obj\main.o" "obj\e_common.o" "obj\e_interrupt.o" "obj\e_po3030k_registers.o" "obj\e_po6030k_registers.o" "obj\e_timers.o" "obj\e_calc_po3030k.o" "obj\e_calc_po6030k.o" "obj\e_calc_po8030d.o" "obj\e_po8030d_registers.o" "obj\e_lsm330.o" "obj\e_led.o" "obj\e_I2C_master_module.o" "obj\e_I2C_protocol.o" "obj\e_accelerometer.o" "obj\e_ad_conv.o" "obj\e_prox.o" "obj\behaviours.o" "obj\avoidProximity.o" "obj\hideSeekSearch.o" "obj\randdir.o" "obj\random_movement.o" "obj\trackLocation.o" "obj\bug_util.o" -o"Demo.cof" -Wl,-L"C:\Program Files\Microchip\MPLAB C30\lib",--script="p30f6014A.gld",--defsym=__MPLAB_BUILD=1,--heap=512

obj/e_init_port.o : ../../library/acc_gyro/e_lsm330.h ../../library/motor_led/e_epuck_ports.h c:/program\ files\ (x86)/microchip/mplab\ c30/support/h/p30F6014A.h ../../library/I2C/e_I2C_master_module.h ../../library/I2C/e_I2C_protocol.h c:/program\ files\ (x86)/microchip/mplab\ c30/support/h/p30F6014A.h ../../library/motor_led/e_epuck_ports.h ../../library/motor_led/e_init_port.c
	$(CC) -mcpu=30F6014A -x c -c ".\..\..\library\motor_led\e_init_port.c" -o".\obj\e_init_port.o" -I"..\..\library" -g -Wall

obj/e_init_uart1.o : c:/program\ files\ (x86)/microchip/mplab\ c30/support/inc/p30F6014A.inc ../../library/uart/e_epuck_ports.inc ../../library/uart/e_init_uart1.s
	$(CC) -mcpu=30F6014A -c -I"..\..\library" ".\..\..\library\uart\e_init_uart1.s" -o".\obj\e_init_uart1.o" -Wa,-I".\..\..\library\codec",-I".\..\..\library\uart",-g

obj/e_uart1_rx_char.o : c:/program\ files\ (x86)/microchip/mplab\ c30/support/inc/p30F6014A.inc ../../library/uart/e_uart1_rx_char.s
	$(CC) -mcpu=30F6014A -c -I"..\..\library" ".\..\..\library\uart\e_uart1_rx_char.s" -o".\obj\e_uart1_rx_char.o" -Wa,-I".\..\..\library\codec",-I".\..\..\library\uart",-g

obj/e_uart1_tx_char.o : c:/program\ files\ (x86)/microchip/mplab\ c30/support/inc/p30F6014A.inc ../../library/uart/e_uart1_tx_char.s
	$(CC) -mcpu=30F6014A -c -I"..\..\library" ".\..\..\library\uart\e_uart1_tx_char.s" -o".\obj\e_uart1_tx_char.o" -Wa,-I".\..\..\library\codec",-I".\..\..\library\uart",-g

obj/e_agenda.o : c:/program\ files\ (x86)/microchip/mplab\ c30/include/yvals.h c:/program\ files\ (x86)/microchip/mplab\ c30/include/stdlib.h c:/program\ files\ (x86)/microchip/mplab\ c30/support/h/p30F6014A.h ../../library/motor_led/e_epuck_ports.h ../../library/motor_led/advance_one_timer/e_agenda.h ../../library/motor_led/advance_one_timer/e_agenda.c
	$(CC) -mcpu=30F6014A -x c -c "..\..\library\motor_led\advance_one_timer\e_agenda.c" -o".\obj\e_agenda.o" -I"..\..\library" -g -Wall

obj/e_motors.o : c:/program\ files\ (x86)/microchip/mplab\ c30/include/yvals.h c:/program\ files\ (x86)/microchip/mplab\ c30/include/stdlib.h ../../library/motor_led/advance_one_timer/e_agenda.h c:/program\ files\ (x86)/microchip/mplab\ c30/support/h/p30F6014A.h ../../library/motor_led/e_epuck_ports.h ../../library/motor_led/advance_one_timer/e_motors.c
	$(CC) -mcpu=30F6014A -x c -c "..\..\library\motor_led\advance_one_timer\e_motors.c" -o".\obj\e_motors.o" -I"..\..\library" -g -Wall

obj/main.o : bug_util.h trackLocation.h hideSeekSearch.h behaviours.h ../../library/motor_led/e_epuck_ports.h ../../library/I2C/e_I2C_master_module.h ../../library/I2C/e_I2C_protocol.h c:/program\ files\ (x86)/microchip/mplab\ c30/support/h/p30F6014A.h ../../library/I2C/e_I2C_master_module.h ../../library/motor_led/advance_one_timer/e_led.h ../../library/camera/fast_2_timer/e_poxxxx.h ../../library/a_d/e_prox.h ../../library/a_d/e_ad_conv.h ../../library/uart/e_uart_char.h ../../library/motor_led/advance_one_timer/e_agenda.h ../../library/motor_led/advance_one_timer/e_motors.h c:/program\ files\ (x86)/microchip/mplab\ c30/support/h/p30F6014A.h ../../library/motor_led/e_epuck_ports.h ../../library/motor_led/e_init_port.h c:/program\ files\ (x86)/microchip/mplab\ c30/include/string.h c:/program\ files\ (x86)/microchip/mplab\ c30/include/yvals.h c:/program\ files\ (x86)/microchip/mplab\ c30/include/stdio.h c:/program\ files\ (x86)/microchip/mplab\ c30/support/h/p30f6014A.h main.c
	$(CC) -mcpu=30F6014A -x c -c "main.c" -o".\obj\main.o" -I"..\..\library" -g -Wall

obj/e_common.o : ../../library/motor_led/e_init_port.h ../../library/motor_led/e_epuck_ports.h c:/program\ files\ (x86)/microchip/mplab\ c30/support/h/p30F6014A.h ../../library/I2C/e_I2C_master_module.h ../../library/I2C/e_I2C_protocol.h c:/program\ files\ (x86)/microchip/mplab\ c30/support/h/p30F6014A.h ../../library/motor_led/e_epuck_ports.h ../../library/camera/fast_2_timer/e_po8030d.h ../../library/camera/fast_2_timer/e_po6030k.h ../../library/camera/fast_2_timer/e_poxxxx.h ../../library/camera/fast_2_timer/e_po3030k.h ../../library/camera/fast_2_timer/e_poxxxx.h ../../library/camera/fast_2_timer/e_common.c
	$(CC) -mcpu=30F6014A -x c -c ".\..\..\library\camera\fast_2_timer\e_common.c" -o".\obj\e_common.o" -I"..\..\library" -g -Wall

obj/e_interrupt.o : c:/program\ files\ (x86)/microchip/mplab\ c30/support/inc/p30F6014A.inc ../../library/camera/fast_2_timer/e_interrupt.s
	$(CC) -mcpu=30F6014A -c -I"..\..\library" ".\..\..\library\camera\fast_2_timer\e_interrupt.s" -o".\obj\e_interrupt.o" -Wa,-I".\..\..\library\codec",-I".\..\..\library\uart",-g

obj/e_po3030k_registers.o : ../../library/camera/fast_2_timer/e_poxxxx.h ../../library/camera/fast_2_timer/e_po3030k.h c:/program\ files\ (x86)/microchip/mplab\ c30/support/h/p30F6014A.h ../../library/motor_led/e_epuck_ports.h c:/program\ files\ (x86)/microchip/mplab\ c30/support/h/p30F6014A.h ../../library/I2C/e_I2C_master_module.h ../../library/I2C/e_I2C_protocol.h ../../library/camera/fast_2_timer/e_po3030k_registers.c
	$(CC) -mcpu=30F6014A -x c -c ".\..\..\library\camera\fast_2_timer\e_po3030k_registers.c" -o".\obj\e_po3030k_registers.o" -I"..\..\library" -g -Wall

obj/e_po6030k_registers.o : ../../library/camera/fast_2_timer/e_poxxxx.h ../../library/camera/fast_2_timer/e_po6030k.h c:/program\ files\ (x86)/microchip/mplab\ c30/support/h/p30F6014A.h ../../library/motor_led/e_epuck_ports.h c:/program\ files\ (x86)/microchip/mplab\ c30/support/h/p30F6014A.h ../../library/I2C/e_I2C_master_module.h ../../library/I2C/e_I2C_protocol.h ../../library/camera/fast_2_timer/e_po6030k_registers.c
	$(CC) -mcpu=30F6014A -x c -c ".\..\..\library\camera\fast_2_timer\e_po6030k_registers.c" -o".\obj\e_po6030k_registers.o" -I"..\..\library" -g -Wall

obj/e_timers.o : ../../library/camera/fast_2_timer/e_poxxxx.h ../../library/camera/fast_2_timer/e_po3030k.h c:/program\ files\ (x86)/microchip/mplab\ c30/support/h/p30F6014A.h ../../library/motor_led/e_epuck_ports.h c:/program\ files\ (x86)/microchip/mplab\ c30/support/h/p30F6014A.h ../../library/camera/fast_2_timer/e_timers.c
	$(CC) -mcpu=30F6014A -x c -c ".\..\..\library\camera\fast_2_timer\e_timers.c" -o".\obj\e_timers.o" -I"..\..\library" -g -Wall

obj/e_calc_po3030k.o : ../../library/motor_led/e_init_port.h ../../library/motor_led/e_epuck_ports.h c:/program\ files\ (x86)/microchip/mplab\ c30/support/h/p30F6014A.h ../../library/I2C/e_I2C_master_module.h ../../library/I2C/e_I2C_protocol.h c:/program\ files\ (x86)/microchip/mplab\ c30/support/h/p30F6014A.h ../../library/motor_led/e_epuck_ports.h ../../library/camera/fast_2_timer/e_poxxxx.h ../../library/camera/fast_2_timer/e_po3030k.h ../../library/camera/fast_2_timer/e_poxxxx.h ../../library/camera/fast_2_timer/e_calc_po3030k.c
	$(CC) -mcpu=30F6014A -x c -c ".\..\..\library\camera\fast_2_timer\e_calc_po3030k.c" -o".\obj\e_calc_po3030k.o" -I"..\..\library" -g -Wall

obj/e_calc_po6030k.o : ../../library/motor_led/e_init_port.h ../../library/motor_led/e_epuck_ports.h c:/program\ files\ (x86)/microchip/mplab\ c30/support/h/p30F6014A.h ../../library/I2C/e_I2C_master_module.h ../../library/I2C/e_I2C_protocol.h c:/program\ files\ (x86)/microchip/mplab\ c30/support/h/p30F6014A.h ../../library/motor_led/e_epuck_ports.h ../../library/camera/fast_2_timer/e_poxxxx.h ../../library/camera/fast_2_timer/e_po6030k.h ../../library/camera/fast_2_timer/e_calc_po6030k.c
	$(CC) -mcpu=30F6014A -x c -c ".\..\..\library\camera\fast_2_timer\e_calc_po6030k.c" -o".\obj\e_calc_po6030k.o" -I"..\..\library" -g -Wall

obj/e_calc_po8030d.o : ../../library/motor_led/e_init_port.h ../../library/motor_led/e_epuck_ports.h c:/program\ files\ (x86)/microchip/mplab\ c30/support/h/p30F6014A.h ../../library/I2C/e_I2C_master_module.h ../../library/I2C/e_I2C_protocol.h c:/program\ files\ (x86)/microchip/mplab\ c30/support/h/p30F6014A.h ../../library/motor_led/e_epuck_ports.h ../../library/camera/fast_2_timer/e_poxxxx.h ../../library/camera/fast_2_timer/e_po8030d.h ../../library/camera/fast_2_timer/e_calc_po8030d.c
	$(CC) -mcpu=30F6014A -x c -c "..\..\library\camera\fast_2_timer\e_calc_po8030d.c" -o".\obj\e_calc_po8030d.o" -I"..\..\library" -g -Wall

obj/e_po8030d_registers.o : ../../library/camera/fast_2_timer/e_poxxxx.h ../../library/camera/fast_2_timer/e_po8030d.h c:/program\ files\ (x86)/microchip/mplab\ c30/support/h/p30F6014A.h ../../library/motor_led/e_epuck_ports.h c:/program\ files\ (x86)/microchip/mplab\ c30/support/h/p30F6014A.h ../../library/I2C/e_I2C_master_module.h ../../library/I2C/e_I2C_protocol.h ../../library/camera/fast_2_timer/e_po8030d_registers.c
	$(CC) -mcpu=30F6014A -x c -c "..\..\library\camera\fast_2_timer\e_po8030d_registers.c" -o".\obj\e_po8030d_registers.o" -I"..\..\library" -g -Wall

obj/e_lsm330.o : c:/program\ files\ (x86)/microchip/mplab\ c30/include/stdio.h c:/program\ files\ (x86)/microchip/mplab\ c30/include/yvals.h c:/program\ files\ (x86)/microchip/mplab\ c30/include/string.h ../../library/uart/e_uart_char.h ../../library/acc_gyro/e_lsm330.h c:/program\ files\ (x86)/microchip/mplab\ c30/support/h/p30F6014A.h ../../library/motor_led/e_epuck_ports.h c:/program\ files\ (x86)/microchip/mplab\ c30/support/h/p30F6014A.h ../../library/I2C/e_I2C_master_module.h ../../library/I2C/e_I2C_protocol.h ../../library/acc_gyro/e_lsm330.c
	$(CC) -mcpu=30F6014A -x c -c "..\..\library\acc_gyro\e_lsm330.c" -o".\obj\e_lsm330.o" -I"..\..\library" -g -Wall

obj/e_led.o : ../../library/motor_led/advance_one_timer/e_agenda.h c:/program\ files\ (x86)/microchip/mplab\ c30/support/h/p30F6014A.h ../../library/motor_led/e_epuck_ports.h ../../library/motor_led/advance_one_timer/e_led.c
	$(CC) -mcpu=30F6014A -x c -c "..\..\library\motor_led\advance_one_timer\e_led.c" -o".\obj\e_led.o" -I"..\..\library" -g -Wall

obj/e_I2C_master_module.o : c:/program\ files\ (x86)/microchip/mplab\ c30/support/h/p30F6014A.h ../../library/I2C/e_I2C_master_module.h ../../library/I2C/e_I2C_master_module.c
	$(CC) -mcpu=30F6014A -x c -c "..\..\library\I2C\e_I2C_master_module.c" -o".\obj\e_I2C_master_module.o" -I"..\..\library" -g -Wall

obj/e_I2C_protocol.o : c:/program\ files\ (x86)/microchip/mplab\ c30/support/h/p30F6014A.h ../../library/motor_led/e_epuck_ports.h c:/program\ files\ (x86)/microchip/mplab\ c30/support/h/p30F6014A.h ../../library/I2C/e_I2C_master_module.h ../../library/I2C/e_I2C_protocol.h ../../library/I2C/e_I2C_protocol.c
	$(CC) -mcpu=30F6014A -x c -c "..\..\library\I2C\e_I2C_protocol.c" -o".\obj\e_I2C_protocol.o" -I"..\..\library" -g -Wall

obj/e_accelerometer.o : ../../library/a_d/e_accelerometer.h c:/program\ files\ (x86)/microchip/mplab\ c30/support/h/p30F6014A.h ../../library/motor_led/e_epuck_ports.h ../../library/a_d/e_ad_conv.h ../../library/a_d/e_accelerometer.c
	$(CC) -mcpu=30F6014A -x c -c "..\..\library\a_d\e_accelerometer.c" -o".\obj\e_accelerometer.o" -I"..\..\library" -g -Wall

obj/e_ad_conv.o : c:/program\ files\ (x86)/microchip/mplab\ c30/support/h/p30F6014A.h ../../library/motor_led/e_epuck_ports.h ../../library/a_d/e_ad_conv.c
	$(CC) -mcpu=30F6014A -x c -c "..\..\library\a_d\e_ad_conv.c" -o".\obj\e_ad_conv.o" -I"..\..\library" -g -Wall

obj/e_prox.o : ../../library/a_d/e_prox.h c:/program\ files\ (x86)/microchip/mplab\ c30/support/h/p30F6014A.h ../../library/motor_led/e_epuck_ports.h ../../library/a_d/e_ad_conv.h ../../library/a_d/e_prox.c
	$(CC) -mcpu=30F6014A -x c -c "..\..\library\a_d\e_prox.c" -o".\obj\e_prox.o" -I"..\..\library" -g -Wall

obj/behaviours.o : c:/program\ files\ (x86)/microchip/mplab\ c30/include/stdlib.h c:/program\ files\ (x86)/microchip/mplab\ c30/include/string.h c:/program\ files\ (x86)/microchip/mplab\ c30/include/yvals.h c:/program\ files\ (x86)/microchip/mplab\ c30/include/stdio.h ../../library/motor_led/e_epuck_ports.h ../../library/I2C/e_I2C_master_module.h ../../library/I2C/e_I2C_protocol.h c:/program\ files\ (x86)/microchip/mplab\ c30/support/h/p30F6014A.h ../../library/I2C/e_I2C_master_module.h ../../library/a_d/e_prox.h ../../library/a_d/e_ad_conv.h ../../library/motor_led/advance_one_timer/e_led.h ../../library/camera/fast_2_timer/e_poxxxx.h ../../library/uart/e_uart_char.h ../../library/motor_led/advance_one_timer/e_agenda.h ../../library/motor_led/advance_one_timer/e_motors.h c:/program\ files\ (x86)/microchip/mplab\ c30/support/h/p30F6014A.h ../../library/motor_led/e_epuck_ports.h ../../library/motor_led/e_init_port.h behaviours.c
	$(CC) -mcpu=30F6014A -x c -c "behaviours.c" -o".\obj\behaviours.o" -I"..\..\library" -g -Wall

obj/avoidProximity.o : ../../library/a_d/e_prox.h ../../library/motor_led/e_init_port.h c:/program\ files\ (x86)/microchip/mplab\ c30/support/h/p30F6014A.h ../../library/motor_led/e_epuck_ports.h avoidProximity.c
	$(CC) -mcpu=30F6014A -x c -c "avoidProximity.c" -o".\obj\avoidProximity.o" -I"..\..\library" -g -Wall

obj/hideSeekSearch.o : ../../library/motor_led/e_epuck_ports.h ../../library/I2C/e_I2C_master_module.h ../../library/I2C/e_I2C_protocol.h c:/program\ files\ (x86)/microchip/mplab\ c30/support/h/p30F6014A.h ../../library/I2C/e_I2C_master_module.h ../../library/a_d/e_prox.h ../../library/a_d/e_ad_conv.h ../../library/motor_led/advance_one_timer/e_led.h ../../library/camera/fast_2_timer/e_poxxxx.h ../../library/uart/e_uart_char.h ../../library/motor_led/advance_one_timer/e_agenda.h ../../library/motor_led/advance_one_timer/e_motors.h c:/program\ files\ (x86)/microchip/mplab\ c30/support/h/p30F6014A.h ../../library/motor_led/e_epuck_ports.h ../../library/motor_led/e_init_port.h trackLocation.h hideSeekSearch.c
	$(CC) -mcpu=30F6014A -x c -c "hideSeekSearch.c" -o".\obj\hideSeekSearch.o" -I"..\..\library" -g -Wall

obj/randdir.o : c:/program\ files\ (x86)/microchip/mplab\ c30/include/math.h randdir.h randdir.c
	$(CC) -mcpu=30F6014A -x c -c "randdir.c" -o".\obj\randdir.o" -I"..\..\library" -g -Wall

obj/random_movement.o : randdir.h ../../library/a_d/e_prox.h ../../library/a_d/e_ad_conv.h ../../library/motor_led/advance_one_timer/e_motors.h c:/program\ files\ (x86)/microchip/mplab\ c30/support/h/p30F6014A.h ../../library/motor_led/e_epuck_ports.h ../../library/motor_led/e_init_port.h random_movement.h c:/program\ files\ (x86)/microchip/mplab\ c30/include/time.h c:/program\ files\ (x86)/microchip/mplab\ c30/include/math.h c:/program\ files\ (x86)/microchip/mplab\ c30/include/yvals.h c:/program\ files\ (x86)/microchip/mplab\ c30/include/stdlib.h c:/program\ files\ (x86)/microchip/mplab\ c30/support/h/p30f6014A.h random_movement.c
	$(CC) -mcpu=30F6014A -x c -c "random_movement.c" -o".\obj\random_movement.o" -I"..\..\library" -g -Wall

obj/trackLocation.o : c:/program\ files\ (x86)/microchip/mplab\ c30/include/stdlib.h c:/program\ files\ (x86)/microchip/mplab\ c30/include/string.h c:/program\ files\ (x86)/microchip/mplab\ c30/include/yvals.h c:/program\ files\ (x86)/microchip/mplab\ c30/include/stdio.h c:/program\ files\ (x86)/microchip/mplab\ c30/include/math.h ../../library/motor_led/e_epuck_ports.h ../../library/I2C/e_I2C_master_module.h ../../library/I2C/e_I2C_protocol.h c:/program\ files\ (x86)/microchip/mplab\ c30/support/h/p30F6014A.h ../../library/I2C/e_I2C_master_module.h ../../library/a_d/e_prox.h ../../library/a_d/e_ad_conv.h ../../library/motor_led/advance_one_timer/e_led.h ../../library/camera/fast_2_timer/e_poxxxx.h ../../library/uart/e_uart_char.h ../../library/motor_led/advance_one_timer/e_agenda.h ../../library/motor_led/advance_one_timer/e_motors.h c:/program\ files\ (x86)/microchip/mplab\ c30/support/h/p30F6014A.h ../../library/motor_led/e_epuck_ports.h ../../library/motor_led/e_init_port.h trackLocation.c
	$(CC) -mcpu=30F6014A -x c -c "trackLocation.c" -o".\obj\trackLocation.o" -I"..\..\library" -g -Wall

obj/bug_util.o : hideSeekSearch.h trackLocation.h c:/program\ files\ (x86)/microchip/mplab\ c30/support/h/p30F6014A.h ../../library/motor_led/e_epuck_ports.h ../../library/I2C/e_I2C_master_module.h ../../library/I2C/e_I2C_protocol.h c:/program\ files\ (x86)/microchip/mplab\ c30/support/h/p30F6014A.h ../../library/I2C/e_I2C_master_module.h ../../library/motor_led/advance_one_timer/e_led.h ../../library/uart/e_uart_char.h ../../library/a_d/e_prox.h c:/program\ files\ (x86)/microchip/mplab\ c30/include/string.h c:/program\ files\ (x86)/microchip/mplab\ c30/include/yvals.h c:/program\ files\ (x86)/microchip/mplab\ c30/include/stdio.h c:/program\ files\ (x86)/microchip/mplab\ c30/include/math.h bug_util.c
	$(CC) -mcpu=30F6014A -x c -c "bug_util.c" -o".\obj\bug_util.o" -I"..\..\library" -g -Wall

clean : 
	$(RM) "obj\e_init_port.o" "obj\e_init_uart1.o" "obj\e_uart1_rx_char.o" "obj\e_uart1_tx_char.o" "obj\e_agenda.o" "obj\e_motors.o" "obj\main.o" "obj\e_common.o" "obj\e_interrupt.o" "obj\e_po3030k_registers.o" "obj\e_po6030k_registers.o" "obj\e_timers.o" "obj\e_calc_po3030k.o" "obj\e_calc_po6030k.o" "obj\e_calc_po8030d.o" "obj\e_po8030d_registers.o" "obj\e_lsm330.o" "obj\e_led.o" "obj\e_I2C_master_module.o" "obj\e_I2C_protocol.o" "obj\e_accelerometer.o" "obj\e_ad_conv.o" "obj\e_prox.o" "obj\behaviours.o" "obj\avoidProximity.o" "obj\hideSeekSearch.o" "obj\randdir.o" "obj\random_movement.o" "obj\trackLocation.o" "obj\bug_util.o" "Demo.cof" "Demo.hex"

