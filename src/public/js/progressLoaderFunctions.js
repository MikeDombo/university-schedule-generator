let createListeners = () => {
    let getCollapses = (expanded) => {
        if (expanded) {
            return $('.collapse.in').not('.navbar-collapse').toArray();
        }
        return $('.collapse:not(.in)').not('.navbar-collapse').toArray();
    };

    $(document).on("click", ".btn-expand", (e) => {
        getCollapses(false).forEach((v) => {
            $(v).addClass("in");
        });
        $(e.target).text(' Collapse All Schedules')
                   .removeClass("glyphicon-collapse-down btn-expand")
                   .addClass("glyphicon-collapse-up btn-collapse");
    });

    $(document).on("click", ".btn-collapse", (e) => {
        getCollapses(false).forEach((v) => {
            $(v).collapse("toggle");
        });
        getCollapses(true).forEach((v) => {
            $(v).removeClass("in");
        });
        $(e.target).text(' Expand All Schedules')
                   .addClass("glyphicon-collapse-down btn-expand")
                   .removeClass("glyphicon-collapse-up btn-collapse");
    });

    $(document).on("click", ".btn-calview", (e) => {
        $(e.target).text(' List View')
                   .addClass("glyphicon-list btn-listview")
                   .removeClass("glyphicon-calendar btn-calview");
        $('#list-view').addClass("hide");
        $('#calendar-view').removeClass("hide");
    });

    $(document).on("click", ".btn-listview", (e) => {
        $(e.target).text(' Calendar View')
                   .removeClass("glyphicon-list btn-listview")
                   .addClass("glyphicon-calendar btn-calview");
        $('#list-view').removeClass("hide");
        $('#calendar-view').addClass("hide");
    });
};

$(document).ready(() => {
    $('[data-toggle="popover"]').popover();
    createListeners();
});

let getParameterByName = (name, url) => {
    if (!url) {
        url = window.location.href;
    }
    name = name.replace(/[\\[\]]/g, "\\$&");
    const regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) {
        return null;
    }
    if (!results[2]) {
        return '';
    }
    return results[2].replace(/\\+/g, " ");
};

window.getParameterByName = getParameterByName;

let makeProgress = (count) => {
    let customElement = $("<img src='loading-spinner.gif' /><h3>", {
        id: "countdown",
        text: ""
    });

    let makeFunnyWord = () => {
        let random = [
            ["Recalibrating", "Excavating", "Acquiring", "Extracting", "Computing", "Deflummoxing", "Binding",
                "Serving", "Routing", "Distributing", "Sampling", "Servicing", "Repairing", "Discombobulating",
                "Processing", "Preprocessing"],
            ["Flux", "Data", "Spline", "Storage", "Plasma", "Cache", "Laser", "Extra Large", "Ethernet", "WiFi",
                "Wireless", "Sample", "Computational", "Local", "Integral"],
            ["Capacitor", "Conductor", "Assembler", "Detector", "Post-processor", "Integrator", "Computer", "Disk",
                "Server", "Router", "Calculator"]
        ];

        let verb = random[0][Math.floor(Math.random() * random[0].length)];
        let adjective = random[1][Math.floor(Math.random() * random[1].length)];
        let noun = random[2][Math.floor(Math.random() * random[2].length)];
        return verb + " " + adjective + " " + noun;
    };

    let sensible = ["Sending Data to Server", "Processing", "Making Schedules", "Wrangling Bits",
        "A Few Bits Tried to Escape, but We Caught Them", "It's Still Faster Than You Could Do It",
        "Counting Down from Inifinity", "Reticulating Splines",
        "Searching for the Answer to Life, the Universe, and Everything",
        "Checking Gravitational Constant in Your Locale"];

    customElement.text(sensible[0]);
    $("#results").LoadingOverlay("show", {
        minSize: "200px",
        size: "100%",
        custom: customElement,
        image: ""
    });

    let ptr = 0;
    let writer = '';

    let interval = setInterval(() => {
        count--;
        if (count > 10 && count % 5 === 4 && ptr < sensible.length) {
            writer = sensible[ptr++];
            customElement.text(writer);
        }
        else if (count > 10 && count % 5 === 4 && ptr >= sensible.length) {
            writer = makeFunnyWord();
            customElement.text(writer);
        }
        else if (count <= 10 && count > 0) {
            writer = "Maximum Execution Time Almost Complete " + count + " Seconds Remain";
            customElement.text(writer);
        }
        else if (count <= 0) {
            clearInterval(interval);
        }
    }, 1000);

    $.ajax({
        url: 'newSched.php',
        type: 'GET',
        data: {"i": getParameterByName("i")},
        dataType: 'html',
        timeout: (count * 1000) + 5000,
        cache: false,
        success: (data) => {
            $('#results').html(data).LoadingOverlay("hide");
            clearInterval(interval);
        },
        error: (e) => {
            console.log(e);
            clearInterval(interval);
            if (e.status === 404 || e.status === 500 || e.statusText === "timeout") {
                $("#results").LoadingOverlay("hide")
                             .html("<div style=\"text-align: center;\">" +
                                 "<h2>Execution Time Exceeded</h2><h3>Try Again with Fewer Courses</h3>" +
                                 "</div>");
            }
            else {
                alert("Something went wrong!");
            }
        }
    });
};

window.makeProgress = makeProgress;
