from snap7.logo import Logo
import time

Logo_7 = False

class PlcService:
    def __init__(self, plc_ip):
        self.plc_ip = plc_ip

    def write_alarm_on(self):
        plc = Logo()
        plc.connect(self.plc_ip, 0x0300, 0x0200)

        if plc.get_connected():
            self.logger.debug("PLC is connected")
            value_s = 0b10
            plc.write("V1104.0", value_s)
            # value_s = 0b1
            # plc.write("V1104.0", value_s)
            # time.sleep(0.05)
            # value_s = 0b0
            # plc.write("V1104.1", value_s)
            print(f"read M1 must be 1 - check: {str(plc.read('V1104.0'))}")
        else:
            self.logger.error("Conncetion failed")

        plc.disconnect()
        self.logger.debug("PLC is disconnected")
        plc.destroy()

    def write_alarm_off(self):
        plc = Logo()
        plc.connect(self.plc_ip, 0x0300, 0x0200)

        if plc.get_connected():
            self.logger.debug("PLC is connected")
            value_s = 0b01
            plc.write("V1104.0", value_s)
            # value_s = 0b0
            # plc.write("V1104.0", value_s)
            # time.sleep(0.05)
            # value_s = 0b1
            # plc.write("V1104.1", value_s)
            print(f"read M2 must be 1 - check: {str(plc.read('V1104.1'))}")
        else:
            self.logger.error("Conncetion failed")
        plc.disconnect()
        self.logger.debug("PLC is disconnected")
        plc.destroy()
