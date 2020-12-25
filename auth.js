$(document).ready(function(){

    var idUser = ''; 
    var urlCurrent = window.location.href;
    var regV = 'auth';     // Р РЋРІвЂљВ¬Р  Р’В°Р  Р’В±Р  Р’В»Р  РЎвЂўР  Р вЂ¦ gi
    var result = urlCurrent.match(regV);  // Р  РЎвЂ”Р  РЎвЂўР  РЎвЂР РЋР С“Р  РЎвЂќ Р РЋРІвЂљВ¬Р  Р’В°Р  Р’В±Р  Р’В»Р  РЎвЂўР  Р вЂ¦Р  Р’В° Р  Р вЂ  Р РЋР вЂ№Р РЋР вЂљР  Р’В»
    if (!result) {
        if(Cookies.get("user-id-auth")) {
            idUser = Cookies.get("user-id-auth");
            if(idUser == "переход на сайт...") {Cookies.set("user-id-auth", null)}
        }else{
            Cookies.set("user-lasturl", urlCurrent);
            document.location.href = regV;
        } 
    } else {
        window.mySuccessFunction = function($form){
            let idUser = $('.js-successbox').html();
            
            if(idUser != "error"){ 
                let id = $("input[name='id']").attr('value');
                Cookies.set('user-id-auth', id);
                console.log(idUser, id);
                setTimeout( () => {
                    document.location.href = '/index.html';
                }, 2000); 
            
            }else{
                location.reload();
            }
        }
    }

});