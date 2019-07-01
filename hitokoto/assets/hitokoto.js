if ($('.breadcrumb').length > 0) {
    $('.breadcrumb').append("<p class='hitokoto'></p>");
} else {
    $('.content-header').append("<div class='breadcrumb'><p class='hitokoto'></p></div>");
}

fetch("https://v1.hitokoto.cn?encode=text").then(function(response) {
    return response.text();
}).then(function(data) {
    document.getElementsByClassName("hitokoto")[0].innerHTML = data;
}).
catch(function(err) {

});
