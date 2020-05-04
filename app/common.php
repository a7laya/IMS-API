<?php
// 应用公共文件



// ===== 抛出异常 =====
function ApiException($msg = '请求错误',$errorCode = 20000,$statusCode = 404)
{
    abort($errorCode, $msg,[
        'statusCode' => $statusCode
    ]);
}

// ===== 成功返回 =====
function showSuccess($data = '',$msg = 'ok',$code = 200){
    return json([ 'msg' => $msg, 'data' => $data ],$code);
}

// ===== 失败返回 =====
function showError($msg = 'error',$code = 400){
    return json([ 'msg' => $msg ],$code);
}

// ===== 获取数组指定key的value =====
function getValByKey($key, $arr, $default = false){
    return array_key_exists($key, $arr) ? $arr[$key] : $default;
}


/**
 * 登陆 - (设置并存储token)
 * @param  array $param 参数配置(data, password, tag, expire)
 * @return array $user
 */
function cms_login(array $param) {
    // 获取参数
    $data = getValByKey('data', $param);
    if(!$data) return false;
    // 标签分组
    $tag = getValByKey('tag', $param, 'manager');
    // 是否返回密码
    $password = getValByKey('password', $param);
    // 登陆有效时间 0为永久
    $expire = getValByKey('expire', $param, 0);

    // 配置缓存 - 在 '\config\cms.php' 里面配置
    $CacheClass = \think\facade\Cache::store(config('cms.'.$tag.'.token.store'));
    // 生成唯一的token
    $token = sha1(md5(uniqid(md5(microtime(true)), true)));
    // 拿到当前的用户数据
    $user = is_object($data) ? $data->toArray() : $data;

    // // 获取之前token并删除(防止重复登录)
    // $token = getValByKey('token', $param);
    // $beforeToken = $CacheClass->get($tag.'_'.$user['id']);
    // // 删除之前token对应的用户信息
    // if($beforeToken){
    //     cms_logout([
    //         'token' => $beforeToken,
    //         'tag' => $tag
    //     ]);
    // }

    // 存储token -  根据 token 存 用户数据 (类似于前端的本地存储中的set和get)
    $CacheClass->set($tag.'_'.$token, $user, $expire);
    // 存储用户id - 根据id 存 token
    $CacheClass->set($tag.'_'.$user['id'], $token, $expire);
    // 隐藏密码 - 如果$password为false 移除$user里面的password
    if(!$password) unset($user['password']);
    // 返回token
    $user['token'] = $token;
    return $user;
}


/**
 * 登出 - (清除缓存\token)
 */
function cms_logout(array $param) {
    $tag = getValByKey('tag', $param, 'manager');
    $token = getValByKey('token', $param);
    // 配置缓存 - 在 '\config\cms.php' 里面配置
    $CacheClass = \think\facade\Cache::store(config('cms.'.$tag.'.token.store'));
    // 通过token获取并清除用户信息
    $user = $CacheClass->pull($tag.'_'.$token);
    // 通过用户id清除token
    if(!empty($user)) $CacheClass->pull($tag.'_'.$user['id']);
    unset($user['password']);
    return $user;
}

/**
 * token校验 - (获取用户信息)
 * @param  array $param 参数配置(tag, token, password)
 * @return array $user
 */
function cms_getUser(array $param) {
    $tag = getValByKey('tag', $param, 'manager');
    $token = getValByKey('token', $param);
    $password = getValByKey('password', $param);
    $user = \think\facade\Cache::store(config('cms.'.$tag.'.token.store'))->get($tag.'_'.$token);
    if(!$password) unset($user['password']);
    return $user;
}