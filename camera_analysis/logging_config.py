import logging

def configure_logging():
    # Configure basic logging for the main application
    logging.basicConfig(level=logging.DEBUG,
                        format='%(asctime)s - %(levelname)s - %(message)s',
                        # filename='app.log',filemode='a'
                        )

    # # Create a dedicated message log Logger
    # message_logger = logging.getLogger('MessageLogger')
    # message_handler = logging.FileHandler('message.log')
    # message_formatter = logging.Formatter('%(asctime)s - %(levelname)s - %(message)s')
    # message_handler.setFormatter(message_formatter)
    # message_logger.addHandler(message_handler)
    # message_logger.setLevel(logging.INFO)

    # # Create a dedicated time log Logger
    # time_logger = logging.getLogger('TimeLogger')
    # #time_handler = logging.FileHandler('time.log')
    # time_handler = logging.StreamHandler()
    # time_formatter = logging.Formatter('%(asctime)s - %(message)s')
    # time_handler.setFormatter(time_formatter)
    # time_logger.addHandler(time_handler)
    # time_logger.setLevel(logging.INFO)

    # Add console output to facilitate viewing logs on the console
    # console_handler = logging.StreamHandler()
    # console_handler.setFormatter(logging.Formatter('%(asctime)s - %(levelname)s - %(message)s'))
    # logging.getLogger().addHandler(console_handler)
