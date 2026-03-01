<?php

namespace app\controllers;

use app\models\Attachment;
use app\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class DownloadController extends Controller
{
   public function actionIndex($attachment_id)
   {
        $attachment = Attachment::findOne($attachment_id);

        Yii::$app->response->sendFile($attachment->file_path);
   }
}
