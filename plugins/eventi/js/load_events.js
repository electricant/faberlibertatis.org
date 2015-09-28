/*
 * Javascript file to be used within files that display the events.
 * 
 * The basic idea is to have a <div> element within the document that will be
 * filled asyncronously by the routines stored in this file.
 * When this file is loaded it will scan the document for divs whose id is
 * 'recent_events' and/or 'all_events' and will fill them automagically.
 */

// put only the most recent events into the provided <div>
function putMostRecentEvents(divId) {
	var divEl = document.getElementById(divId);

	if (!divEl) {
		console.log("load_events.js/putMostRecentEvents > Invalid div: " +
			divId);
		return;
	}

	var xhr = new XMLHttpRequest();
	xhr.open("GET", "/eventi", true);
	xhr.onload = function (e) {
		if (xhr.readyState === 4) {
			if (xhr.status === 200) {
				divEl.innerHTML = xhr.responseText;
			} else {
				console.error(xhr.statusText);
			}
		}
	};
	xhr.onerror = function (e) {console.error(xhr.statusText);};
	xhr.send(null);
}
// put all the events into the provided <div>
function putAllEvents(divId) {
    var divEl = document.getElementById(divId);

    if (!divEl) {
        console.log("load_events.js/putAllEvents > Invalid div: " + divId);
        return;
    }

    var xhr = new XMLHttpRequest();
    xhr.open("GET", "/eventi/all", true);
    xhr.onload = function (e) {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                divEl.innerHTML = xhr.responseText;
            } else {
                console.error(xhr.statusText);
            }
        }
    };
    xhr.onerror = function (e) {console.error(xhr.statusText);};
    xhr.send(null);
}
putMostRecentEvents('recent_events');
putAllEvents('all_events');
