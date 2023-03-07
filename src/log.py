
log_callback = print


def dbg(msg):
    log_callback(msg)


def set_callback(func):
    global log_callback
    if func is None:
        log_callback = print
    else:
        log_callback = func
