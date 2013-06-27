<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


//Контроллер принимающий и обрабатывающий Ajax запросы
class Ajaxprod extends CI_Controller {
    
    private $adminemail = '';
    
    public function __construct() {
        parent::__construct();
    }
    
    //Добавление в карзину
    public function addbasket(){
        $postdata = $this->input->post();
        if(!empty($postdata)){
            $objcart = $this->getobjcart($postdata['id'],$postdata['options']);
            $nameidcart = $this->products_model->gtprodtitle($postdata['id']);
            $ssize = $this->sortsizeprod($this->products_model->getproducts(),$postdata['id']);
            switch($objcart){
                case 'updatecomlete':
                    break;
                default :
                    $config = array(
                       'id'      => $postdata['id'],
                       'qty'     => (int)$postdata['qty'],
                       'price'   => $postdata['price'],
                       'name'    => 'product_'.$postdata['id'],
                       'options' => array('size' => $ssize, 'name' => $nameidcart));
                    $this->cart->insert($config);
                    break;
            }
        } else {redirect(base_url());}
        $this->getbasketdata();
    }
    
    //Добавление параметров
    private function sortsizeprod($products, $id){
        foreach($products as $product){
            if($product['id'] == $id){
                foreach($product['size'] as $key => $value){
                    $data[$value] = $value;
                }
                return $data;
            }
        }
    }
    
    //Извлечение всей карзины
    public function getbasketdata(){
        echo json_encode($this->basket_lib->getbasketdata());
    }
    
    //Изменение количества товара в карзине
    private function getobjcart($id,$options){
        $basket = $this->basket_lib->getbasketdata();
        if(empty($basket['contentbasket'])){return 'none';}
        else {
            foreach($basket['contentbasket'] as $key => $value){
                if($value['id'] == $id){
                   $qty = $value['qty'] + 1;
                   $config = array(
                       'rowid'      => $key,
                       'qty'        => $qty
                       );
                   $this->cart->update($config);
                   return 'updatecomlete';
                }
            }
            return 'addnew';
        }
    }
    
    //Удаление элемента из карзины
    public function delitembasket(){
        $postdata = $this->input->post();
        $config = array(
                    'rowid'      => $postdata['rowid'],
                    'qty'        => 0);
        $this->cart->update($config);
        $this->getbasketdata();
    }
    
    //--------------------------------
    public function getfinaldata(){
        $data = $this->display_lib->datacount();
        echo json_encode($data);
    }
    
    //Обработка и отправка на почту администратора информации о заказе
    public function addsenddatazakaz(){
        $postdata = $this->input->post();
        if(empty($postdata['senddata']['contaktdata'][0])){echo '0';}
        else {
            $sendemail['name']      = $postdata['senddata']['contaktdata'][0];
            $sendemail['phone']     = $postdata['senddata']['contaktdata'][1];
            $sendemail['email']     = $postdata['senddata']['contaktdata'][2];
            $sendemail['adress']    = $postdata['senddata']['contaktdata'][3];
            $sendemail['comment']   = $postdata['senddata']['contaktdata'][4];
            $summazakaza            = $postdata['senddata']['summa'];
            $tovari                 = $this->products_model->razbortovarazakaza($postdata['senddata']['tovari'],$summazakaza,$sendemail);
            $arrmsg['clientdata']   = $sendemail;
            $arrmsg['tovari']       = $tovari;
            $arrmsg['summa']        = $summazakaza;
            $this->sendemaildata($arrmsg);
            $this->cart->destroy();
            echo $this->getbasketdata();
        }
    }
    
    //Фактическая отправка письма
    private function sendemaildata($arrmsg){
        $this->load->library('email');
        $this->email->from($arrmsg['clientdata']['email'], $arrmsg['clientdata']['name']);
        $this->email->to($this->adminemail); 
        $this->email->subject('На вашем сайте оставили заказ.');
        $this->email->message($arrmsg['tovari']);
        $this->email->send();
    }
    
    //Отправка копии письма пользователю
    public function sendmleuser(){
        $postdata = $this->input->post();
        if(empty($postdata)){echo 0;}
        else {
            $this->load->library('email');
            $this->email->from($postdata['feedmail'], $postdata['name']);
            $this->email->to($this->adminemail); 
            $this->email->subject('Письмо с сайта.');
            $this->email->message($postdata['msg']);
            $this->email->send();
            echo 'Письмо отправлено администратору';
        }
    }
    
}
