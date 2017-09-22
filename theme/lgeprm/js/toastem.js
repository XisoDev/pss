

function xisoApi(url,params){
    var baseUrl = "../../../";
    var output = {};
    console.log("send params : ");
    console.log(params);
    $.ajax({
        cache:false,
        async:false,
        type : 'POST',
        url : baseUrl + url + "?returnType=json",
        data: params,
        dataType : 'json',
        contentType: "application/x-www-form-urlencoded",
        error: function(xhr, status, error){
            console.log(xhr);
            console.log(status);
            console.log(error);
            toastem.error('서버와의 통신 중 오류가 발생했습니다.');
        },
        success : function(retObj){
            if(retObj.error < 0){
                toastem.error('Code : ' + retObj.error + "<br />" + retObj.message);
            }
            output = retObj;
        }
    });
    return output;
}


var toastem = (function($){

    var normal = function(content){
        var item = $('<div class="notification normal"><span>'+content+'</span></div>');
        $("#toastem").append($(item));
        $(item).animate({"right":"12px"}, "fast");
        setInterval(function(){
            $(item).animate({"right":"-400px"},function(){
                $(item).remove();
            });
        },3000);
    };



    var success = function(content){
        var item = $('<div class="notification success"><span>'+content+'</span></div>');
        $("#toastem").append($(item));
        $(item).animate({"right":"12px"}, "fast");
        setInterval(function(){
            $(item).animate({"right":"-400px"},function(){
                $(item).remove();
            });
        },2000);
    };

    var error = function(content){
        var item = $('<div class="notification error"><span>'+content+'</span></div>');
        $("#toastem").append($(item));
        $(item).animate({"right":"12px"}, "fast");
        setInterval(function(){
            $(item).animate({"right":"-400px"},function(){
                $(item).remove();
            });
        },5000);
    };

    var call = function(content,callnum,call_srl){
        var html = '<div class="notification call" call_srl="'+call_srl+'">'
        html += '<span class="licon"><i class="pe-7s-call"></i></span>전화수신 : <span>'+content+'</span><hr />';
        html += '<span class="iconmenu" onclick="document.location.href=\'#customer?callnum='+callnum+'\';removeToast('+call_srl+')"><i class="pe-7f-user"></i><span>정보관리</span></span>';
        html += '<span class="iconmenu" onclick="document.location.href=\'#order?callnum='+callnum+'\';removeToast('+call_srl+')"><i class="pe-7f-news-paper"></i><span>주문입력</span></span>';
        html += '<span class="iconmenu" onclick="document.location.href=\'#consulting?callnum='+callnum+'\';removeToast('+call_srl+')"><i class="pe-7f-comment"></i><span>상담입력</span></span>';
        html += '<span class="iconmenu" onclick="removeToast($(this).parent())"><i class="pe-7s-close"></i><span>닫기</span></span>';
        html += '</div>';
        var item = $(html);
        $("#toastem").append($(item));
        $(item).animate({"right":"12px"}, "fast");
    };

    return{
        normal: normal,
        success: success,
        error: error,
        call: call
    };

})(jQuery);

function removeToast(call_srl){
    //클릭시 해당 콜을 읽은상태로 변경후 박스제거

    $(".notification[call_srl="+call_srl+"]").animate({"right":"-400px"},function(){
        $(this).remove();
    });
}

$("#live-call").click(function(){
    if($(this).is(":checked")){
        toastem.normal("콜 수신을 중단합니다.");
    }else{
        toastem.success("라이브 콜을 수신합니다.");
    }
});
//계속 서버에서 새 전화를 확인하고, 새전화 목록에 없는 notification은 제거.
//toastem.call("<b>010-5759-5999</b> (회선1) 4회",'01057595999',1);