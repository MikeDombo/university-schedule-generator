var crns = "";
if(typeof $("#crns").val() !== "undefined" && $("#crns").val().length > 0){
	crns = $("#crns").val().replace(/[,]+/g, '').replace(/ +(?= )/g, '').split(" ");
}
var subjects = {
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

function browse(){
	$.each(subjects, function (k, subjFullName){
		var $subjPanel = $("#subj-list-template").clone().removeClass("hide").removeAttr('id').addClass("subj-" + k);
		$subjPanel.find(".panel-title").text(subjFullName + " (" + k + ")");
		$("#subj-list").append($subjPanel);
	});
}

function loadCourses($newPanel, data, v, num){
	var initial = data.substring(data.indexOf("</span>" + v.FOS + " " + num));
	var end = initial.substring(0, initial.indexOf('<!--close inner-content-wrap'));
	var title = end.substring(v.FOS.length + 9 + v["Course Number"].length, end.indexOf("</a>"));
	var descr = end.substring(end.indexOf("Description</div>") + 17);
	descr = descr.substring(0, descr.indexOf("</div>"));
	var units = end.substring(end.indexOf("Units: ") + 7, end.indexOf("</div>"));
	var hasDescr = true;
	if(title.indexOf('Print Courses') > -1){
		hasDescr = false;
		title = v.Title;
		descr = "Course has no description";
		units = "units are unknown";
	}
	var prereq = "";
	if(end.indexOf("Prerequisites</div>") > -1){
		prereq = end.substring(end.indexOf("Prerequisites</div>") + 19);
		prereq = prereq.substring(0, prereq.indexOf("</div>"));
	}
	if(!(v.Title.indexOf("ST:") > -1) && !(v.Title.indexOf("SP:") > -1) && v.FOS !== "FYS" &&
		v["FOS"] !== "WELL" && !(v.FOS === "HIST" && v["Course Number"] === "199") && !(v.FOS === "HIST" &&
			v["Course Number"] === "299") && !(v.FOS === "ENGL" && v["Course Number"] === "299") &&
		!(v.FOS === "BIOL" && v["Course Number"] === "199")){
		if(hasDescr){
			v.Title = title;
		}
	}

	if(v.FOS === "FYS" && "description" in v){
		title = v.displayTitle;
		descr = v.description;
	}
	else if(!("description" in v)){
		title = v.Title;
	}

	var html = "<h4>" + title + "</h4><p>" + descr + "</p><p>Units: " + units + "</p>";
	if(prereq !== ""){
		html = html + "<p>Prerequisites: " + prereq + "</p>";
	}
	$newPanel.find("#title").text(v.FOS + " " + v["Course Number"] + " | " + title);
	$newPanel.find(".panel-body").html(html);
	$newPanel.find("#button")
	         .attr("data-fos", v.FOS)
	         .attr("data-coursenum", v["Course Number"])
	         .attr("data-coursename", v.Title)
	         .attr("data-displayTitle", v.displayTitle);
	var inCRN = false;
	$.each(v.crns, function (i, v2){
		if(crns.indexOf(v2) > -1){
			inCRN = true;
		}
	});

	if(!v.Available){
		$newPanel.find("#button")
		         .removeClass("btn-success")
		         .removeClass("btn-add-course")
		         .removeClass("glyphicon-plus")
		         .addClass("btn-disable")
		         .text("Course Not Available");
	}
	else if(inCRN){
		$newPanel.find("#button")
		         .removeClass("btn-success")
		         .removeClass("btn-add-course")
		         .removeClass("glyphicon-plus")
		         .addClass("btn-disable")
		         .text("Preregistered");
	}

	$("#course-basket, #course-basket-required")
		.find("li")
		.each(function (){
			if($(this).data("fos") === v.FOS && $(this).data("coursenum") === parseInt(v["Course Number"]) &&
				$(this).data("coursename") === v.Title){
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
}

function fetchBySubj(k){
	$.ajax({
		url: "richmondAPI.php",
		jsonp: "callback",
		dataType: "jsonp",
		data: {
			subj: k
		},
		success: function (courseData){
			courseData = courseData.response;
			$.getJSON('richmondAPI.php?catalog-subj=' + k + '&callback=?', function (data){
				data = data.courses;
				$.each(courseData, function (i, v){
					var $newPanel = $("#subj-list-template2").clone().removeAttr('id');
					var num = v["Course Number"];
					if(num <= 99){
						num = "0" + num;
					}
					$newPanel = loadCourses($newPanel, data, v, num).removeClass("hide");
					$("#subj-list").find(".subj-" + k).find('.main-body').append($newPanel);
				});
			});
		}
	});
}

browse();
var alreadyFetched = [];
$(document).on("click", ".collapse-btn", function (){
	var cls = $(this).parent().attr("class").split(" ");
	cls = cls[cls.length - 1].split("-")[1];
	if(alreadyFetched.indexOf(cls) === -1){
		fetchBySubj(cls);
		alreadyFetched.push(cls);
	}
	$(this).parent().find('.panel-collapse:not(.subj-list-collapse)').collapse('toggle');
});
$(document).on("click", ".collapse-btn-subj", function (){
	$(this).parent().parent().find('.panel-collapse').collapse('toggle');
});

$(function (){
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
});

var $defaultSearchResult = $("#searchResultTemplate");
var $addedTemplate = $("#addedTemplate");
var $buttonRemoveTemplate = $("#basket-remove");

$(document).on("keyup", "#searchField", function (){
	var loc = $("#searchField").val();
	if(loc === ""){
		$("#search-results").empty();
	}
	if(loc.length < 3){
		return;
	}
	$.ajax({
		url: "richmondAPI.php",
		jsonp: "callback",
		dataType: "jsonp",
		data: {
			search: loc
		},
		success: function (courseData){
			courseData = courseData.response;
			$.each(courseData, function (i, v){
				var $newPanel = $defaultSearchResult.clone();
				var num = v["Course Number"];
				var cn = num;

				if(num <= 99){
					num = "0" + num;
				}

				// Transform course number to use in lookup against Richmond API
				if(num > 99){
					cn = cn.substr(0, 1) + "00";
				}

				$.getJSON('richmondAPI.php?catalog-subj=' + v.FOS + '&catalog-level=' + cn + '&callback=?', function (data){
					data = data.courses;
					$newPanel = loadCourses($newPanel, data, v, num).removeAttr('id').removeClass("hide");
					if(i === 0){
						$("#search-results").empty();
					}
					$("#search-results").append($newPanel);
				});
			});
		}
	});
});

$(document).on("click", ".btn-generate", function (){
	function basketItemsToDictionary(data){
		return {
			CourseNum: $(data).data("coursenum"),
			FOS: $(data).data("fos"),
			Title: $(data).data("coursename"),
			displayTitle: $(data).data("displaytitle"),
			requiredCourse: false
		};
	}

	var getCourses = [];

	var $courses = $("#course-basket li");
	var count = $courses.size();
	getCourses = getCourses.concat($courses.toArray().map(basketItemsToDictionary));

	$courses = $("#course-basket-required li");
	count += $courses.size();
	getCourses = getCourses.concat($courses.toArray()
	                                       .map(basketItemsToDictionary)
	                                       .map(function (v){
		                                       v.requiredCourse = true;
		                                       return v;
	                                       }));

	var unwantedTimes = $("#block .blocked-time").toArray().map(function (value){
		return $(value).find("input, .time-display").toArray()
		               .reduce(function (accumulator, currentValue){
			               if(typeof $(currentValue).text() !== "undefined" && $(currentValue).text() !== ""){
				               var times = $(currentValue).text().split(" - ");
				               accumulator.startTime = times[0];
				               accumulator.endTime = times[1];
			               }
			               if($(currentValue).is(':checked')){
				               accumulator[$(currentValue).attr('name')] = $(currentValue).attr('name');
			               }

			               return accumulator;
		               }, {});
	});

	var crns = $("#crns").val().replace(/\D+/g, ',').split(",");
	getCourses = {
		allCourses: getCourses,
		timePref: $("#time-pref").prop('checked'),
		fullClasses: $("#full-classes").prop('checked'),
		preregistered: crns,
		startTime: $("#restrict-slider").text().split(" - ")[0],
		endTime: $("#restrict-slider").text().split(" - ")[1],
		unwantedTimes: unwantedTimes
	};

	if(count > 5){
		window.alert("Trying to generate schedules with this many courses may take a long time, but I will try."
			+ "\n\nThe calculation is allowed take up to 5 minutes, if it takes longer, it will fail.");
	}
	window.location.assign("makeSchedule.php?i=" + encodeURIComponent(JSON.stringify(getCourses)));
});

$(document).on("click", ".btn-jumbo-close", function (){
	$(this).parent().parent().hide();
	document.cookie = "jumbotron=hidden";
});

$(document).on("click", ".btn-remove-course", function (e){
	var $course = $(e.target);
	if(typeof $course.data("fos") === "undefined"){
		$course = $course.parent();
	}
	var fos = $course.data("fos");
	var num = $course.data("coursenum");
	var name = $course.data("coursename");

	function changeClassesToAdd(){
		if($(this).data("fos") === fos && $(this).data("coursenum") === num && $(this).data("coursename") === name){
			$(this).addClass("glyphicon-plus")
			       .addClass("btn-success")
			       .addClass("btn-add-course")
			       .removeClass("btn-danger")
			       .removeClass("glyphicon-minus")
			       .removeClass("btn-remove-course");
		}
	}

	$("#search-results").find("button").each(changeClassesToAdd);
	$(".panel .panel-default button").each(changeClassesToAdd);
	$("#course-basket li, #course-basket-required li").each(function (){
		if($(this).data("fos") === fos && $(this).data("coursenum") === num && $(this).data("coursename") === name){
			$(this).remove();
		}
	});
});

function addCourse(fos, num, name, displaytitle){
	var continuing = true;
	$("#course-basket li, #course-basket-required li").each(function (){
		if($(this).data("fos") === fos && $(this).data("coursenum") === num && $(this).data("coursename") === name){
			continuing = false;
		}
	});
	if(continuing){
		function changeClassesToRemove(){
			if($(this).data("fos") === fos && $(this).data("coursenum") === num && $(this).data("coursename") === name){
				$(this).removeClass("glyphicon-plus");
				$(this).removeClass("btn-success");
				$(this).removeClass("btn-add-course");
				$(this).addClass("btn-danger");
				$(this).addClass("glyphicon-minus");
				$(this).addClass("btn-remove-course");
			}
		}

		$("#search-results button").each(changeClassesToRemove);
		$(".panel .panel-default button").each(changeClassesToRemove);

		var $add = $addedTemplate.clone();
		var $button = $buttonRemoveTemplate.clone().removeClass("hide");
		$add.removeClass("hide")
		    .attr("id", "")
		    .text(fos + " " + num + " | " + name)
		    .append("&nbsp; &nbsp; &nbsp; &nbsp;", $button)
		    .attr("data-fos", fos)
		    .attr("data-coursenum", num)
		    .attr("data-coursename", name)
		    .attr("data-displayTitle", displaytitle);
		$("#course-basket").append($add);
	}
}

$(document).on("click", ".btn-add-course", function (e){
	var $course = $(e.target);
	var fos = $course.data("fos");
	var num = $course.data("coursenum");
	var name = $course.data("coursename");
	var displaytitle = $course.data("displaytitle");
	addCourse(fos, num, name, displaytitle);
});
