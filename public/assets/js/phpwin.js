
var phpwin = {
    busy: function (status) {
        if (status === false) {
            document.body.style.cursor = 'default';
            return;
        }

        document.body.style.cursor = 'wait';
        if (typeof status === 'string') {
            console.log(status);
        }
    },

    alert: function (message) {
        alert(message);
    },
};
