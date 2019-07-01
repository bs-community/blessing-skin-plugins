if (document.querySelectorAll('.breadcrumb').length > 0) {
  document.querySelector('.breadcrumb').innerHTML += '<p class="hitokoto"></p>'
} else {
  document.querySelector('.content-header').innerHTML +=
    '<div class="breadcrumb"><p class="hitokoto"></p></div>'
}

fetch('https://v1.hitokoto.cn?encode=text')
  .then(response => response.text())
  .then(data => {
    document.querySelector('.hitokoto').textContent = data
  })
  .catch(err => {})
