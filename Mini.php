<?php
class Mini
{
    /**
     * @Author: Ferre
     * @create: 2019/9/18 17:11
     * 获取token-保持用户登录 时限为2小时
     * js_code-登陆许可凭证  openid-唯一标识  session_key-会话密钥
     */
    public function actionGetAccess()
    {
        $encryptedData = Yii::$app->request->post('encryptedData');
        $code          = Yii::$app->request->post('code');
        $iv            = Yii::$app->request->post('iv', 'r7BXXKkLb8qrSNn05n0qiA==');
        $session_data  = $this->getMiniOpenId($code);    //获取session_key会话秘钥
        if (empty($session_data['session_key'])){
            $session_data['session_key'] = 'tiihtNczf5v6AKRyjwEUhQ==';  //兼容测试
        }

        if (empty($encryptedData)){
            $encryptedData="CiyLU1Aw2KjvrjMdj8YKliAjtP4gsMZM
                QmRzooG2xrDcvSnxIMXFufNstNGTyaGS
                9uT5geRa0W4oTOb1WT7fJlAC+oNPdbB+
                3hVbJSRgv+4lGOETKUQz6OYStslQ142d
                NCuabNPGBzlooOmB231qMM85d2/fV6Ch
                evvXvQP8Hkue1poOFtnEtpyxVLW1zAo6
                /1Xx1COxFvrc2d7UL/lmHInNlxuacJXw
                u0fjpXfz/YqYzBIBzD6WUfTIF9GRHpOn
                /Hz7saL8xz+W//FRAUid1OksQaQx4CMs
                8LOddcQhULW4ucetDf96JcR3g0gfRK4P
                C7E/r7Z6xNrXd2UIeorGj5Ef7b1pJAYB
                6Y5anaHqZ9J6nKEBvB4DnNLIVWSgARns
                /8wR2SiRS7MNACwTyrGvt9ts8p12PKFd
                lqYTopNHR1Vf7XjfhQlVsAJdNiKdYmYV
                oKlaRv85IfVunYzO0IKXsyl7JCUjCpoG
                20f0a04COwfneQAGGwd5oa+T8yO5hzuy
                Db/XcxxmK01EpqOyuxINew==";
        }

        $pc      = new \WXBizDataCrypt(Yii::$app->params['miniAppId'], $session_data['session_key']);
        $errCode = $pc->decryptData($encryptedData, $iv, $data );
        if ($errCode == 0) {    //获取成功
            $data = json_decode($data, true);
            if (!empty($data['phone'])){    //TODO 验证后端+注册

            }
            self::returnSuccess(1,'成功', $data);
        } else {
            self::returnError($errCode, '失败');
        }
    }

    /**
     * @Author: Ferre
     * @create: 2019/9/18 17:11
     * @param $code
     * @return mixed
     * 获取openId + 2小时验证刷新sessionKey
     */
    public function getMiniOpenId($code)
    {
        $miniSessionKey = Yii::$app->cache->get('miniSessionKey');
        if (empty($miniSessionKey)){
            $appId     = Yii::$app->params['miniAppId'];
            $appSecret = Yii::$app->params['miniAppSecret'];
            $url       = "https://api.weixin.qq.com/sns/jscode2session?appid=$appId&secret=$appSecret&js_code=$code&grant_type=authorization_code";
            $apiData   = file_get_contents($url);
            Yii::$app->cache->set('miniSessionKey', $apiData, 7199);    //2小时
        }else{
            $apiData = $miniSessionKey;
        }
        return json_decode($apiData, true);
    }
}
