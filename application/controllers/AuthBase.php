<?php
/**
 * AuthBase.php
 * Created By Colorful
 * Date:2018/4/25
 * Time:上午8:58
 */
class AuthBaseController extends AbstractController
{
    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        if( !$this->isLogin() ) {
            Common_Request::response(-1009, '你没有登录');
        }
    }

    /**
     * 根据header中的token判断登录态
     * @return bool
     */
    public function isLogin()
    {
        if( empty( $this->headers['access_user_token'] ) ) {
            return false;
        }

        // 实例化aes类
        $aes_obj = new Common_Aes( Yaf_Registry::get('config')->keys->aes_salt );
        // 从header中获取access_user_token并解密
        $access_user_token = $aes_obj->decrypt($this->headers['access_user_token']);
        if( empty($access_user_token) ) {
            return false;
        }
        // 如果没有 "||", 返回false
        if (!preg_match('/||/', $access_user_token)) {
            return false;
        }
        list( $token, $uid ) = explode( '||', $access_user_token );
        $bis_model = new BisModel();
        $bis_user_info = $bis_model->getUserByToken($token, $uid);
        if( date('Y-m-d H:i:s') > $bis_user_info['token_timeout'] ) {
            return false;
        }
        // 保存用户信息到成员变量中
        $this->bis_user = $bis_user_info;
        return true;
    }
}
