let getCookie = (cname) => {
    let name = cname + "=";
    let ca = document.cookie.split(';');
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) === 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
};

let isJSON = (str) => {
    try {
        JSON.parse(str);
    }
    catch (e) {
        return false;
    }
    return true;
};

let sliderIntToStartEndTimes = (val0, val1) => {
    let minutes0 = parseInt(val0, 10) % 60;
    let hours0 = Math.floor(parseInt(val0, 10) / 60) % 24;
    let minutes1 = parseInt(val1, 10) % 60;
    let hours1 = Math.floor(parseInt(val1, 10) / 60) % 24;

    let startTime = getTime(hours0, minutes0);
    let endTime = getTime(hours1, minutes1);
    return startTime + " - " + endTime;
};

let setSpanTime = ($id) => {
    let val0 = $id.slider("values", 0);
    let val1 = $id.slider("values", 1);
    $id.parent().children().last().text(sliderIntToStartEndTimes(val0, val1));
};

let getTime = (hours, minutes) => {
    let time = null;
    minutes = minutes + "";
    if (hours < 12) {
        time = "AM";
    }
    else {
        time = "PM";
    }
    if (hours === 0) {
        hours = 12;
    }
    if (hours > 12) {
        hours = hours - 12;
    }
    if (minutes.length === 1) {
        minutes = "0" + minutes;
    }
    return hours + ":" + minutes + " " + time;
};

let slideTime = (event, ui) => {
    let val0 = ui.values[0];
    let val1 = ui.values[1];
    $(event.target).parent().children().last().text(sliderIntToStartEndTimes(val0, val1));
};

$(document).ready(() => {
    let crns = "";
    let crnSelector = $("#crns");
    if (typeof crnSelector.val() !== "undefined" && crnSelector.val().length > 0) {
        crns = crnSelector.val().replace(/[,]+/g, '').replace(/ +(?= )/g, '').split(" ");
    }
    let subjects = {
        "ACCT": "Accounting",
        "AMST": "American Studies",
        "ANTH": "Anthropology",
        "ARAB": "Arabic",
        "ARTH": "Art History",
        "BIOL": "Biology",
        "BMB": "Biochemistry",
        "BUAD": "Business Administration",
        "CHEM": "Chemistry",
        "CHIN": "Chinese Program",
        "CJ": "Criminal Justice",
        "CLAC": "Cultures and Languages Across the Curriculum",
        "CLCV": "Classical Studies",
        "CLSC": "Classical Studies",
        "CMSC": "Computer Science",
        "DANC": "Dance",
        "ECON": "Economics",
        "EDUC": "Education",
        "ENGL": "English",
        "ENVR": "Environmental Studies",
        "FIN": "Finance",
        "FMST": "Film Studies",
        "FREN": "French Program",
        "FYS": "First Year Seminar",
        "GEOG": "Geography",
        "GERM": "German Studies Program",
        "GREK": "Greek",
        "HCS": "Healthcare Studies",
        "HIST": "History",
        "IBUS": "International Business",
        "IDST": "Interdisciplinary Studies",
        "IS": "International Studies",
        "ITAL": "Italian Studies Program",
        "JAPN": "Japanese Program",
        "JOUR": "Journalism",
        "JWST": "Jewish Studies",
        "LAIS": "Latin American, Latino and Iberian Studies",
        "LATN": "Latin",
        "LDST": "Leadership Studies",
        "LLC": "Languages, Literatures and Cultures",
        "MATH": "Mathematics",
        "MGMT": "Management",
        "MKT": "Marketing",
        "MSAP": "Music-Applied",
        "MSCL": "Military Science and Leadership",
        "MSEN": "Music-Ensemble",
        "MUS": "Music",
        "PHIL": "Philosophy",
        "PHYS": "Physics",
        "PLSC": "Political Science",
        "PPEL": "Philosophy Politics Economics and Law",
        "PSYC": "Psychology",
        "RELG": "Religious Studies",
        "RHCS": "Rhetoric and Communication Studies",
        "RUSN": "Russian Studies Program",
        "SDLC": "Languages, Literatures and Cultures",
        "SOC": "Sociology",
        "SPCS": "School of Professional and Continuing Studies",
        "SWAH": "Languages, Literatures and Cultures",
        "THTR": "Theatre",
        "UNIV": "University Seminar",
        "VMAP": "Visual and Media Arts Practice",
        "WELL": "Wellness Program",
        "WGSS": "Women, Gender and Sexuality Studies"
    };

    let $defaultSearchResult = $("#searchResultTemplate");
    let $addedTemplate = $("#addedTemplate");
    let $buttonRemoveTemplate = $("#basket-remove");
    let alreadyFetched = [];

    let browse = () => {
        Object.keys(subjects).forEach((k) => {
            let subjFullName = subjects[k];
            let $subjPanel = $("#subj-list-template").clone().removeClass("hide").removeAttr('id')
                                                     .addClass("subj-" + k);
            $subjPanel.find(".panel-title").text(subjFullName + " (" + k + ")");
            $("#subj-list").append($subjPanel);
        });
    };

    let loadCourses = ($newPanel, data, v, num) => {
        let initial = data.substring(data.indexOf("</span>" + v.FOS + " " + num));
        let end = initial.substring(0, initial.indexOf('<!--close inner-content-wrap'));
        let title = end.substring(v.FOS.length + 9 + v["Course Number"].length, end.indexOf("</a>"));
        let descr = end.substring(end.indexOf("Description</div>") + 17);
        descr = descr.substring(0, descr.indexOf("</div>"));
        let units = end.substring(end.indexOf("Units: ") + 7, end.indexOf("</div>"));
        let hasDescr = true;
        if (title.indexOf('Print Courses') > -1) {
            hasDescr = false;
            title = v.Title;
            descr = "Course has no description";
            units = "units are unknown";
        }
        let prereq = "";
        if (end.indexOf("Prerequisites</div>") > -1) {
            prereq = end.substring(end.indexOf("Prerequisites</div>") + 19);
            prereq = prereq.substring(0, prereq.indexOf("</div>"));
        }
        if (!(v.Title.indexOf("ST:") > -1) && !(v.Title.indexOf("SP:") > -1) && v.FOS !== "FYS" &&
            v["FOS"] !== "WELL" && !(v.FOS === "HIST" && v["Course Number"] === "199") && !(v.FOS === "HIST" &&
                v["Course Number"] === "299") && !(v.FOS === "ENGL" && v["Course Number"] === "299") &&
            !(v.FOS === "BIOL" && v["Course Number"] === "199")) {
            if (hasDescr) {
                v.Title = title;
            }
        }

        if (v.FOS === "FYS" && "description" in v) {
            title = v.displayTitle;
            descr = v.description;
        }
        else if (!("description" in v)) {
            title = v.Title;
        }

        let html = "<h4>" + title + "</h4><p>" + descr + "</p><p>Units: " + units + "</p>";
        if (prereq !== "") {
            html = html + "<p>Prerequisites: " + prereq + "</p>";
        }
        $newPanel.find("#title").text(v.FOS + " " + v["Course Number"] + " | " + title);
        $newPanel.find(".panel-body").html(html);
        $newPanel.find("#button")
                 .attr("data-fos", v.FOS)
                 .attr("data-coursenum", v["Course Number"])
                 .attr("data-coursename", v.Title)
                 .attr("data-displayTitle", v.displayTitle);

        let inCRN = false;
        v.crns.forEach((v) => {
            if (crns.indexOf(v) > -1) {
                inCRN = true;
            }
        });

        if (!v.Available) {
            $newPanel.find("#button")
                     .removeClass("btn-success")
                     .removeClass("btn-add-course")
                     .removeClass("glyphicon-plus")
                     .addClass("btn-disable")
                     .text("Course Not Available");
        }
        else if (inCRN) {
            $newPanel.find("#button")
                     .removeClass("btn-success")
                     .removeClass("btn-add-course")
                     .removeClass("glyphicon-plus")
                     .addClass("btn-disable")
                     .text("Preregistered");
        }

        $("#course-basket, #course-basket-required")
            .find("li").toArray()
            .forEach((li) => {
                li = $(li);
                if (li.data("fos") === v.FOS && li.data("coursenum") === parseInt(v["Course Number"]) &&
                    li.data("coursename") === v.Title) {
                    $newPanel.find("#button")
                             .removeClass("glyphicon-plus")
                             .removeClass("btn-success")
                             .removeClass("btn-add-course")
                             .addClass("btn-danger")
                             .addClass("glyphicon-minus")
                             .addClass("btn-remove-course");
                }
            });

        return $newPanel;
    };

    let fetchBySubj = (k) => {
        $.ajax({
            url: "richmondAPI.php",
            jsonp: "callback",
            dataType: "jsonp",
            data: {
                subj: k
            },
            success: (courseData) => {
                courseData = courseData.response;
                $.getJSON('richmondAPI.php?catalog-subj=' + k + '&callback=?', (data) => {
                    data = data.courses;
                    courseData.forEach((v) => {
                        let $newPanel = $("#subj-list-template2").clone().removeAttr('id');
                        let num = v["Course Number"];
                        if (num <= 99) {
                            num = "0" + num;
                        }
                        $newPanel = loadCourses($newPanel, data, v, num).removeClass("hide");
                        $("#subj-list").find(".subj-" + k).find('.main-body').append($newPanel);
                    });
                });
            }
        });
    };

    let addCourse = (fos, num, name, displaytitle) => {
        let continuing = true;
        $("#course-basket li, #course-basket-required li").toArray().forEach((course) => {
            course = $(course);
            if (course.data("fos") === fos && course.data("coursenum") === num && course.data("coursename") === name) {
                continuing = false;
            }
        });
        if (!continuing) {
            return;
        }

        let changeClassesToRemove = (course) => {
            course = $(course);
            if (course.data("fos") === fos && course.data("coursenum") === num && course.data("coursename") === name) {
                course.removeClass("glyphicon-plus")
                      .removeClass("btn-success")
                      .removeClass("btn-add-course")
                      .addClass("btn-danger")
                      .addClass("glyphicon-minus")
                      .addClass("btn-remove-course");
            }
        };

        $("#search-results button").toArray().forEach(changeClassesToRemove);
        $(".panel .panel-default button").toArray().forEach(changeClassesToRemove);

        let $add = $addedTemplate.clone();
        let $button = $buttonRemoveTemplate.clone().removeClass("hide");
        $add.removeClass("hide")
            .attr("id", "")
            .text(fos + " " + num + " | " + name)
            .append("&nbsp; &nbsp; &nbsp; &nbsp;", $button)
            .attr("data-fos", fos)
            .attr("data-coursenum", num)
            .attr("data-coursename", name)
            .attr("data-displayTitle", displaytitle);
        $("#course-basket").append($add);
    };

    let createListeners = () => {
        $(document).on("click", ".collapse-btn", (event) => {
            event = $(event.target);
            while (!event.attr("class").includes("subj-")) {
                event = event.parent();
            }
            let cls = event.attr("class").split(" ");
            cls = cls[cls.length - 1].split("-")[1];
            if (alreadyFetched.indexOf(cls) === -1) {
                fetchBySubj(cls);
                alreadyFetched.push(cls);
            }
            event.find('.panel-collapse:not(.subj-list-collapse)').collapse('toggle');
        });

        $(document).on("click", ".collapse-btn-subj", (event) => {
            event = $(event.target);
            while (!event.attr("class").includes("collapse-btn-subj")) {
                event = event.parent();
            }
            event.parent().parent().find('.panel-collapse').collapse('toggle');
        });

        $(document).on("keyup", "#searchField", () => {
            let loc = $("#searchField").val();
            let $resultsSelector = $("#search-results");
            if (loc === "") {
                $resultsSelector.empty();
            }
            if (loc.length < 3) {
                return;
            }
            $.ajax({
                url: "richmondAPI.php",
                jsonp: "callback",
                dataType: "jsonp",
                data: {
                    search: loc
                },
                success: (courseData) => {
                    courseData = courseData.response;
                    courseData.forEach((v, i) => {
                        let $newPanel = $defaultSearchResult.clone();
                        let num = v["Course Number"];
                        let cn = num;

                        if (num <= 99) {
                            num = "0" + num;
                        }

                        // Transform course number to use in lookup against Richmond API
                        if (num > 99) {
                            cn = cn.substr(0, 1) + "00";
                        }

                        $.getJSON('richmondAPI.php?catalog-subj=' + v.FOS + '&catalog-level=' + cn + '&callback=?', (data) => {
                            data = data.courses;
                            $newPanel = loadCourses($newPanel, data, v, num).removeAttr('id').removeClass("hide");
                            if (i === 0) {
                                $resultsSelector.empty();
                            }
                            $resultsSelector.append($newPanel);
                        });
                    });
                }
            });
        });

        $(document).on("click", ".btn-generate", () => {
            let basketItemsToDictionary = (data) => {
                return {
                    CourseNum: $(data).data("coursenum"),
                    FOS: $(data).data("fos"),
                    Title: $(data).data("coursename"),
                    displayTitle: $(data).data("displaytitle"),
                    requiredCourse: false
                };
            };

            let getCourses = [];

            let $courses = $("#course-basket").find("li");
            let count = $courses.size();
            getCourses = getCourses.concat($courses.toArray().map(basketItemsToDictionary));

            $courses = $("#course-basket-required").find("li");
            count += $courses.size();
            getCourses = getCourses.concat($courses.toArray()
                                                   .map(basketItemsToDictionary)
                                                   .map((v) => {
                                                       v.requiredCourse = true;
                                                       return v;
                                                   }));

            let unwantedTimes = $("#block").find(".blocked-time").toArray().map((value) => {
                return $(value).find("input, .time-display").toArray()
                               .reduce((accumulator, currentValue) => {
                                   currentValue = $(currentValue);
                                   if (typeof currentValue.text() !== "undefined" && currentValue.text() !== "") {
                                       let times = currentValue.text().split(" - ");
                                       accumulator.startTime = times[0];
                                       accumulator.endTime = times[1];
                                   }
                                   if (currentValue.is(':checked')) {
                                       accumulator[currentValue.attr('name')] = currentValue.attr('name');
                                   }

                                   return accumulator;
                               }, {});
            });

            let crns = $("#crns").val().replace(/\D+/g, ',').split(",");
            getCourses = {
                allCourses: getCourses,
                timePref: $("#time-pref").prop('checked'),
                fullClasses: $("#full-classes").prop('checked'),
                preregistered: crns,
                startTime: $("#restrict-slider").text().split(" - ")[0],
                endTime: $("#restrict-slider").text().split(" - ")[1],
                unwantedTimes: unwantedTimes
            };

            if (count > 5) {
                window.alert("Trying to generate schedules with this many courses may take a long time, but I will try."
                    + "\n\nThe calculation is allowed take up to 5 minutes, if it takes longer, it will fail.");
            }
            window.location.assign("makeSchedule.php?i=" + encodeURIComponent(JSON.stringify(getCourses)));
        });

        $(document).on("click", ".btn-jumbo-close", () => {
            $(".jumbotron").hide();
            document.cookie = "jumbotron=hidden";
        });

        $(document).on("click", ".btn-add-course", (e) => {
            let $course = $(e.target);
            addCourse($course.data("fos"), $course.data("coursenum"),
                $course.data("coursename"), $course.data("displaytitle"));
        });

        $(document).on("click", ".btn-load-history", (e) => {
            let c = getCookie("history");
            c = JSON.parse(decodeURIComponent(c));
            c = c[$(e.target).data('history-id')];
            $.each(c, (i, v) => {
                if (typeof v === "object" && "Title" in v) {
                    if ("displayTitle" in v) {
                        v.displayTitle = v.displayTitle.replace(/\+/g, " ");
                    }
                    addCourse(v.FOS, v.CourseNum, v.Title.replace(/\+/g, " "), v.displayTitle);
                }
            });
        });

        $(document).on("click", "#adv", (e) => {
            e.preventDefault(); // Don't scroll to top of page

            if (!$('#advanced').is(':visible')) {
                $('#advanced').show(500);
                $('#adv').text('Hide Advanced Options');
                $('#arrow').removeClass("glyphicon-chevron-right").addClass("glyphicon-chevron-down");
            }
            else {
                $('#advanced').hide(500);
                $('#adv').text('Show Advanced Options');
                $('#arrow').removeClass("glyphicon-chevron-down").addClass("glyphicon-chevron-right");
            }
        });

        $(document).on("click", ".btn-remove-block-time", (e) => {
            $(e.target).closest(".blocked-time").remove();
        });

        $(document).on("click", ".btn-remove-course", function (e) {
            let $course = $(e.target);
            if (typeof $course.data("fos") === "undefined") {
                $course = $course.parent();
            }
            let fos = $course.data("fos");
            let num = $course.data("coursenum");
            let name = $course.data("coursename");

            let changeClassesToAdd = (course) => {
                course = $(course);
                if (course.data("fos") === fos && course.data("coursenum") === num && course.data("coursename") === name) {
                    course.addClass("glyphicon-plus")
                          .addClass("btn-success")
                          .addClass("btn-add-course")
                          .removeClass("btn-danger")
                          .removeClass("glyphicon-minus")
                          .removeClass("btn-remove-course");
                }
            };

            $("#search-results").find("button").toArray().forEach(changeClassesToAdd);
            $(".panel .panel-default button").toArray().forEach(changeClassesToAdd);
            $("#course-basket li, #course-basket-required li").toArray().forEach((course) => {
                course = $(course);
                if (course.data("fos") === fos && course.data("coursenum") === num && course.data("coursename") === name) {
                    course.remove();
                }
            });
        });
    };

    createListeners();

    $("#advanced").hide();

    $('#time-pref').bootstrapToggle({
        on: 'Morning',
        off: 'Afternoon',
        offstyle: 'warning'
    });

    $('#full-classes').bootstrapToggle({
        on: 'Yes',
        off: 'No',
        offstyle: 'danger',
        onstyle: 'success'
    });

    if (getCookie("history").length > 0) {
        let c = getCookie("history");
        c = JSON.parse(decodeURIComponent(c));
        c.forEach((v, i) => {
            let $cloned = $("#history-template").clone().removeClass("hide").removeAttr('id');
            $cloned.find(".panel-title").text(v.schedules + " Schedules");
            let courses = "<ul class='list-group'>";
            Object.keys(v).forEach((key) => {
                let v2 = v[key];
                if (typeof v2 === "object" && "Title" in v2) {
                    courses = courses + "<li class='list-group-item'>" + v2.Title.replace(/\+/g, " ") + "</li>";
                }
            });
            courses = courses + "</ul>";
            $cloned.find(".panel-body").html(courses);
            $cloned.find(".btn-load-history").attr('data-history-id', i);
            $("#history").prepend($cloned);
        });
    }

    if (getCookie("jumbotron") !== "hidden") {
        $(".jumbotron").removeClass("hide");
    }

    browse();

});

function initialSetSliders (sliderMin, sliderMax) {
    $(document).on("click", "#add-block-time", () => {
        let $cloned = $("#block-time-template").clone().removeClass("hide").removeAttr('id');
        $cloned.find(".time-slider").slider({
            range: true,
            min: sliderMin,
            max: sliderMax,
            step: 15,
            values: [sliderMin, 600],
            slide: slideTime
        });
        setSpanTime($cloned.find(".time-slider"));
        $("#block").append($cloned);
    });

    $(document).ready(function () {
            let getParameterByName = (name, url) => {
                if (!url) {
                    url = window.location.href;
                }
                name = name.replace(/[\[\]]/g, "\\$&");
                let regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)");
                let results = regex.exec(url);
                if (!results) {
                    return null;
                }
                if (!results[2]) {
                    return '';
                }
                return decodeURIComponent(results[2].replace(/\+/g, " "));
            };
            let paramI = getParameterByName('i');
            if (paramI !== null && paramI !== "" && isJSON(paramI)) {
                let importFromURL = JSON.parse(paramI).unwantedTimes;

                let setInputCheckedByName = (name, element) => {
                    element.find("input[name='" + name + "']").parent().addClass("active");
                    element.find("input[name='" + name + "']").prop('checked', true);
                };

                let getTimeInMinutesFromString = (str) => {
                    let d = new Date();
                    let now = new Date(d.getFullYear(), d.getMonth(), d.getDate(), 0, 0, 0);

                    let time = str.split(" ")[0].split(":");
                    let ampm = str.split(" ")[1];
                    d.setHours(parseInt(time[0]) + (ampm === "PM" ? 12 : 0));
                    d.setMinutes(parseInt(time[1]) || 0);
                    return Math.floor((d - now) / 60000);
                };

                importFromURL.forEach((timeRestrictionValue) => {
                    let $cloned = $("#block-time-template").clone()
                                                           .removeClass("hide")
                                                           .removeAttr('id');
                    timeRestrictionValue.startTime = getTimeInMinutesFromString(timeRestrictionValue.startTime);
                    timeRestrictionValue.endTime = getTimeInMinutesFromString(timeRestrictionValue.endTime);

                    $cloned.find(".time-slider").slider({
                        range: true,
                        min: sliderMin,
                        max: sliderMax,
                        step: 15,
                        values: [timeRestrictionValue.startTime, timeRestrictionValue.endTime],
                        slide: slideTime
                    });
                    setSpanTime($cloned.find(".time-slider"));

                    let days = ["Su", "M", "T", "W", "R", "F", "S"];
                    days.forEach((day) => {
                        if (day in timeRestrictionValue) {
                            setInputCheckedByName(day, $cloned);
                        }
                    });

                    $("#block").append($cloned);
                });
            }
        }
    );
}
