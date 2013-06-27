    
    //Добавление в карзину
    function otpquery(idTovar,priceItem,titleItem,sizeItem){
        $.ajax({
           type: "POST",
           url: "/ajaxprod/addbasket",
           data: {
               id       : idTovar,
               qty      : 1,
               price    : priceItem,
               name     : titleItem,
               options  : sizeItem },
           success: function(response) {
               updbasketdata(response);
           }
        });
    }
    
    //Извлечение всей карзины и копирование ее в глобальный объект
    function getbasketdata(){
        $.ajax({
           type: "POST",
           url: "/ajaxprod/getbasketdata",
           data: {},
           success: function(response) {
               obj = $.parseJSON(response);
           }
        });
    }
