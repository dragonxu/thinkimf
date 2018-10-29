var GM_JQ = document.createElement('script');
GM_JQ.src = '//code.jquery.com/jquery-3.3.1.min.js';
GM_JQ.type = 'text/JavaScript';
document.getElementsByTagName('head')[0].appendChild(GM_JQ);
$("input[disabled]").prop('disabled',0);

//下载视频
$('table a').each(function($k,$v){window.open(($($v).attr('href')))});