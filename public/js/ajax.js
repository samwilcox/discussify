/**
 * Performs an AJAX GET request to the system.
 * @param {string} action - The AJAX action to perform. 
 * @param {object} data - Query string parameters to send with the request.
 * @param {callback} successCallback - The callback function when a response is received.
 */
function ajaxGet(action, data, successCallback) {
    toggleLoadBar('start');
    var html = atob(json.ajax_error_html);

    $.ajax({
        url: ajaxUrl + '?controller=ajax&action=' + action + parseGetData(data),
        type: 'GET',
        success: function(response) {
            successCallback(response);
        },
        error: function (xhr, status, error) {
            html = html.replace("${xhr}", xhr);
            html = html.replace("${status}", status);
            html = html.replace("${error}", error);
            $("#ajax-error-box").html(html);
            openDialog('dialog-ajax-error', 550);
        }
    });

    toggleLoadBar();
}

/**
 * Performs an AJAX POST request to the system.
 * @param {\string} action - The AJAX action to perform.
 * @param {object} data - Parameters to send with the request.
 * @param {callback} callback - The callback function when a response is received.
 */
function ajaxPost(action, data, successCallback) {
    toggleLoadBar('start');

    var html = atob(json.ajax_error_html);

    $.ajax({
        url: ajaxUrl + '?controller=ajax&action=' + action,
        type: 'POST',
        contentType: 'application/json',
        data: data,
        success: function(response) {
            console.log(response);
            successCallback(response);
        },
        error: function(xhr, status, error) {
            html = html.replace("${xhr}", xhr);
            html = html.replace("${status}", status);
            html = html.replace("${error}", error);
            $("#ajax-error-box").html(html);
            openDialog('dialog-ajax-error', 550);
        }
    });

    toggleLoadBar();
}

/**
 * Parses the given data object into a query string format.
 * @param {object} data - The data to parse for query string.
 * @return {string} - query string.
 */
function parseGetData(data) {
    var queryString = "";

    for (var key in data) {
        if (key == 'controller') {
            queryString += '?' + key + '=' + data[key];
        } else {
            queryString += '&' + key + '=' + data[key];
        }
    }

    return queryString;
}

/**
 * Appends an item onto the front of the given object.
 * @param {object} obj - The object to append the parameter to.
 * @param {string} key - The parameter key value.
 * @param {string} value   - The parameter value.
 * @return {object} - Object with parameter appended to the front.
 */
function appendToFront(obj, key, value) {
    var newObj = {};
    newObj[key] = value;

    for (var prop in obj) {
        if (obj.hasOwnProperty(prop)) {
            newObj[prop] = obj[prop];
        }
    }

    return newObj;
}

/**
 * Toggles the top loading bar to animate.
 * @param {string} mode - The toggle mode. 
 */
function toggleLoadBar(mode) {
    if (mode == 'start') {
        $("#ajax-load-bar").animate({width: "100%" }, 5000);
    } else {
        $("#ajax-load-bar").stop();
        $("#ajax-load-bar").css({"width":0});
    }
}

/**
 * Checks the AJAX response for error.
 * @param {mixed} response - AJAX response. 
 */
function checkForError(response) {
    if (response.result != null) {
        if (response.response == 'error') {
            // TO-DO: Will finish later.
        }
    }
}