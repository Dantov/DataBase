<?php
namespace Views\_Options\Controllers;
use Views\_Options\Models\Options;
use Views\_Globals\Controllers\GeneralController;
use Views\vendor\core\Cookies;

class OptionsController extends GeneralController
{

    public string $title = 'Опции ХЮФ 3Д';

    public function beforeAction()
    {
        $request = $this->request;
        if ( $request->isAjax() )
        {
            if ( $widthControl = (int)$request->post('widthControl') ) $this->actionWidthControl($widthControl);
            if ( $noticeActivate = (int)$request->post('noticeActivate') ) $this->actionNoticeToggler($noticeActivate);
            if ( $srcBgImg = $request->post('srcBgImg') ) $this->actionSetBackgroundImage($srcBgImg);
            exit;
        }
    }

    public function action()
    {
        $options = new Options();

        $thisPage = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        if ( $thisPage !== $_SERVER["HTTP_REFERER"] ) {
            $_SESSION['prevPage'] = $_SERVER["HTTP_REFERER"];
        }

        $widthCheck = '';
        if ( $_SESSION['assist']['containerFullWidth'] == 1 ) {
            $widthCheck = 'checked';
        }
        $PushNoticeCheck = '';
        if ( $_SESSION['assist']['PushNotice'] == 1 ) {
            $PushNoticeCheck = 'checked';
        }
        $bgsImg = $options->scanBGFolder();

        for( $i = 0; $i < count($bgsImg); $i++ )
        {
            if ( $bgsImg[$i]['body'] == $_SESSION['assist']['bodyImg'] ) $bgsImg[$i]['checked'] = 'checked';
        }

        $compacts = compact([
            'PushNoticeCheck','bgsImg','widthCheck'
        ]);

        $this->includeJSFile('options.js', ['defer', 'timestamp']);

        return $this->render('options', $compacts);
    }

    protected function actionWidthControl($widthControl)
    {
        $assist = $this->session->getKey('assist');
        $arr['done'] = 0;
        if ( $widthControl === 1 )
        {
            $assist['containerFullWidth'] = 1;
            $strname = "assist[containerFullWidth]";

            $cookie = Cookies::set($strname, "", 1);
            $cookie = Cookies::set($strname, "1", time()+(3600*24*30));
            // setcookie($strname, "", 1, '/', $_SERVER['HTTP_HOST'] );
            // setcookie($strname, 1, time()+(3600*24*30), '/', $_SERVER['HTTP_HOST'] );
            $arr['done'] = 1;
        }
        if ( $widthControl === 2 )
        {
            $assist['containerFullWidth'] = 0;
            $strname = "assist[containerFullWidth]";

            $cookie = Cookies::set($strname, "", 1);
            $cookie = Cookies::set($strname, "1", time()+(3600*24*30));
            // setcookie($strname, "", 1, '/', $_SERVER['HTTP_HOST'] );
            // setcookie($strname, 0, time()+(3600*24*30), '/', $_SERVER['HTTP_HOST'] );
            $arr['done'] = 2;
        }

        $this->session->setKey('assist', $assist);
        echo json_encode($arr);
        exit;
    }

    protected function actionNoticeToggler($noticeActivate)
    {
        $assist = $this->session->getKey('assist');
        $arr['done'] = 0;

        if ( $noticeActivate === 1 )
        {
            $assist['PushNotice'] = 1;
            $strname = "assist[PushNotice]";

            $cookie = Cookies::set($strname, "", 1);
            $cookie = Cookies::set($strname, "1", time()+(3600*24*30));
            // setcookie($strname, "", 1, '/', $_SERVER['HTTP_HOST'] );
            // setcookie($strname, 1, time()+(3600*24*30), '/', $_SERVER['HTTP_HOST'] );
             $arr['done'] = 1;
        }
        if ( $noticeActivate === 2 )
        {
            $assist['PushNotice'] = 2;
            $strname = "assist[PushNotice]";

            $cookie = Cookies::set($strname, "", 1);
            $cookie = Cookies::set($strname, "1", time()+(3600*24*30));
            // setcookie($strname, "", 1, '/', $_SERVER['HTTP_HOST'] );
            // setcookie($strname, 1, time()+(3600*24*30), '/', $_SERVER['HTTP_HOST'] );
             $arr['done'] = 2;
        }

        $this->session->setKey('assist', $assist);
        echo json_encode($arr);
        exit;
    }

    protected function actionSetBackgroundImage($srcBgImg)
    {
        $assist = $this->session->getKey('assist');

        $srcBgImg = trim( strip_tags($_POST['srcBgImg']) );
        $assist['bodyImg'] = $srcBgImg;
        $strname = "assist[bodyImg]";
        
        $cookie = Cookies::set( $strname, "", 1 );
        $cookie = Cookies::set( $strname, $srcBgImg, time()+(3600*24*30) );

        // setcookie($strname, "", 1, '/', $_SERVER['HTTP_HOST'] );
        // setcookie($strname, $_POST['srcBgImg'], time()+(3600*24*30), '/', $_SERVER['HTTP_HOST'] );

        $this->session->setKey('assist', $assist);
        echo json_encode(['done'=>1]);
        exit;
    }

}