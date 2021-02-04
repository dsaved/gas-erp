<?php

class Application
{

    // page controller_action such as delete , list, edit and view. defaults to index if not specified

    public static $controller =  "home";

    

    // page controller_action such as delete , list, edit and view. defaults to index if not specified

    public static $controller_action = "index";

    

    // pages name

    public static $page_name = null;

    public static $subpage_menu = null;

    

    // page field such as the variable to assign data to

    public static $field = null;

    

    // page value such as the data to carry operation on

    public static $value = null;

    

    // site menus such as the data to carry operation on

    public static $menus = array();

    public static $adminmenus = array();


    public function run()
    {

        $url = isset($_GET['param'])? $_GET['param']:"home";

        $url = escape(html_xss_clean($url));

        $url = explode("/", rtrim($url,'/'));



        // all controller controller_class name must start with capital letter

        $url_1 = strtolower((!empty($url[0])) ? $url[0] : self::$controller);   #class

        $url_2 = strtolower((!empty($url[1])) ? $url[1]: null);    #action / method

        $url_3 = (!empty($url[2])) ? $url[2] : null;   #field

        $url_4 = (!empty($url[3])) ? $url[3] : null;   #value

        
        

        // all controller class name must start with capital letter

        $controller_class = ucfirst($url_1);



        //calling method area

        if(class_exists($controller_class,true)){

            $controller = new $controller_class;

            $controller->loadModel($controller_class);

                        

            $page=$url_1;

            $action="index";

            $args=array();

            $field=null;

            $value=null; 


            if(!empty($url_4)){                                 //when url like   page/action/field/value

                $action = $url_2;

                $field = $url_3;

                $value = $url_4;

                $args = array_slice($url,2);

                if(!method_exists($controller,$action)){

                    $this->render_not_found("index",Msg::actionerror($action ,$controller_class));

                }

            }

            elseif (!empty($url_3)) {                               //when url like   page/action/value

                $action = $url_2;

                $value = $url_3;

                if(!method_exists($controller,$action)){    

                    // when url like page/view/3 and 'view' or 'edit' page does not exit

                    if(($action=="view" || $action=="edit")){ 

                        $this->render_not_found("index",Msg::actionerror($action ,$controller_class));

                    }

                    else{                                   //when url like  page/field/value

                        $field=$url_2;

                        $value=$url_3;

                        $args=array_slice($url,1);

                        $action="index";

                        if(!method_exists($controller,$action)){

                            $this->render_not_found("index",Msg::actionerror($action ,$controller_class));

                        }

                    }

                }

                else{                                       //when url like  page/action/pageid

                    $args=array_slice($url,2);

                    $action=$url_2;

                }

            }

            elseif(!empty($url_2)){         

                if(!method_exists($controller,$url_2)){     //when url like  product/productid

                    $args=array_slice($url,1);

                    $action="view";

                    if(!method_exists($controller,$action)){

                        $this->render_not_found("index",Msg::actionerror($action ,$controller_class));

                    }

                }

                else{                                       //when url like  page/action

                    $args=array();

                    $action=$url_2;

                }

            }



            

            // Set Router Page Variables. They can be accessed by calling Router :: $page_variable_name

            self::$page_name=$page;

            self::$controller_action=$action;

            

            self::$field=$field;

            self::$value=$value;

            

            self::$controller=$controller_class;

            

            // call the controller action here

            $controller->$action($args);

        }else{

            $this->render_not_found("index",Msg::nocontroller($controller_class));

        }

    }

    public function render_not_found($page,$message)
    {
        $bcontroller = new BaseController;
        $response = array();
        if (DEVELOPER_MODE) {
            $response['success'] = false;
            $response['message'] = $message; 
        }else{
            $response['success'] = false;
            $response['message'] = "ERROR: 404 not found"; 
        }
        http_response_code(404);
        render_json($response); 
    }

}

