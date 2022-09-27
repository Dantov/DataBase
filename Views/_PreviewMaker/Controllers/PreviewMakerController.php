<?php
/**
 * Date: 16.08.2021
 * Time: 21:18
 */
namespace Views\_PreviewMaker\Controllers;

use Views\_Globals\Controllers\GeneralController;
use Views\_PreviewMaker\Models\PreviewMaker;


class PreviewMakerController extends GeneralController
{

    public $title = 'ХЮФ :: создание превьюшек';



    /**
     * @throws \Exception
     */
    public function beforeAction()
    {
        $request = $this->request;

        if ( $request->isAjax() )
        {
            try
            {
                if ( $request->isPost() )
                {
                    if ( (int)$request->post('start') === 1 )
                    {
                        $response = [
                            'finish' => 1,
                            'message' => (new PreviewMaker())->startOperation($request->post('tabID')),
                        ];
                        exit(json_encode($response));
                    }

                }
            } catch ( \TypeError | \Error | \Exception $e) {
                $this->serverError_ajax($e);
            }

            exit;
        }
    }

    /**
     * @throws \Exception
     */
    public function action()
    {
        $hello = "Preview maker!";



        $this->includeJSFile('prevMaker.js', ['timestamp'] );

        $compacted = compact(['hello','images']);
        return $this->render('previewMaker', $compacted);
    }



}