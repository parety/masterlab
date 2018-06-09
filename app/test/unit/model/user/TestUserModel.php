<?php

namespace main\app\test\unit\model\user;

use PHPUnit\Framework\TestCase;

use main\app\classes\UserAuth;
use main\app\classes\UserLogic;
use main\app\model\user\UserModel;

/**
 * UserModel 测试类
 * User: sven
 */
class TestUserModel extends TestCase
{
    /**
     * 用户数据
     * @var array
     */
    public static $user = [];

    public static function setUpBeforeClass()
    {
        self::$user = self::initUser();
    }

    public static function tearDownAfterClass()
    {
        self::clearData();
    }

    /**
     * 初始化用户
     */
    public static function initUser()
    {
        $username = '190' . mt_rand(12345678, 92345678);
        $originPassword = '123456';
        $password = UserAuth::createPassword($originPassword);

        // 表单数据 $post_data
        $postData = [];
        $postData['username'] = $username;
        $postData['phone'] = $username;
        $postData['email'] = $username . '@masterlab.org';
        $postData['display_name'] = $username;
        $postData['status'] = UserModel::STATUS_NORMAL;
        $postData['password'] = $password;
        $postData['openid'] = md5($username);

        $userModel = new UserModel();
        list($ret, $msg) = $userModel->insert($postData);
        if (!$ret) {
            var_dump('initUser  failed,' . $msg);
            parent::fail('initUser  failed,' . $msg);
            return;
        }
        $user = $userModel->getRowById($msg);
        return $user;
    }

    /**
     * 清除数据
     */
    public static function clearData()
    {
        $userModel = new UserModel();
        $userModel->deleteById(self::$user['uid']);
    }

    /**
     * 测试单例对象
     */
    public function testGetInstance()
    {
        $userModel1 = UserModel::getInstance();
        $userModel2 = UserModel::getInstance();
        $userModel3 = UserModel::getInstance(self::$user['uid']);
        $userModel4 = UserModel::getInstance(self::$user['uid']);

        $this->assertEquals($userModel1, $userModel2);
        $this->assertEquals($userModel3, $userModel4);
        $this->assertNotEquals($userModel1, $userModel3);

        $userModel5 = UserModel::getInstance(self::$user['uid'], true);
        $this->assertFalse($userModel4 === $userModel5);
    }

    /**
     * 测试获取当前用户信息
     */
    public function testGetUser()
    {
        $userModel = UserModel::getInstance(self::$user['uid']);
        $user = $userModel->getUser();
        $this->assertNotEmpty($user);
        foreach ($user as $k => $v) {
            $this->assertEquals($v, self::$user[$k]);
        }
    }

    public function testGetByUid()
    {
        $userModel = UserModel::getInstance();
        $user = $userModel->getByUid(self::$user['uid']);
        $this->assertNotEmpty($user);
        foreach (self::$user as $k => $v) {
            $this->assertEquals($v, $user[$k]);
        }
    }

    public function testGetByOpenid()
    {
        $userModel = UserModel::getInstance();
        $user = $userModel->getByOpenid(self::$user['openid']);
        $this->assertNotEmpty($user);
        foreach (self::$user as $k => $v) {
            $this->assertEquals($v, $user[$k]);
        }
    }

    public function testGetByPhone()
    {
        $userModel = UserModel::getInstance();
        $user = $userModel->getByPhone(self::$user['phone']);
        $this->assertNotEmpty($user);
        foreach (self::$user as $k => $v) {
            $this->assertEquals($v, $user[$k]);
        }
    }

    public function testGetByEmail()
    {
        $userModel = UserModel::getInstance();
        $user = $userModel->getByEmail(self::$user['email']);
        $this->assertNotEmpty($user);
        foreach (self::$user as $k => $v) {
            $this->assertEquals($v, $user[$k]);
        }
    }

    public function testUsername()
    {
        $userModel = UserModel::getInstance();
        $user = $userModel->getByUsername(self::$user['username']);
        $this->assertNotEmpty($user);
        foreach (self::$user as $k => $v) {
            $this->assertEquals($v, $user[$k]);
        }
    }

    public function testGetUsersByIds()
    {
        $userModel = UserModel::getInstance();
        $this->assertEmpty($userModel->getUsersByIds([]));
        $this->assertEmpty($userModel->getUsersByIds(''));

        $newUser = self::initUser();
        $userIDs = [self::$user['uid'], $newUser['uid']];
        $users = $userModel->getUsersByIds($userIDs);
        $this->assertCount(2, $users);
        $this->assertNotEmpty($users[0]);
        $this->assertNotEmpty($users[1]);

        $emails = $userModel->getFieldByIds('email', $userIDs);
        $userModel->deleteById($newUser['uid']);
        $this->assertNotEmpty($emails);
        $this->assertTrue(is_string($emails[0]));
    }

    public function testGetFieldByIds()
    {
        $userModel = UserModel::getInstance();
        $newUser = self::initUser();
        $userIDs = [self::$user['uid'], $newUser['uid']];

        $emails = $userModel->getFieldByIds('email', $userIDs);
        $userModel->deleteById($newUser['uid']);
        $this->assertNotEmpty($emails);
        $this->assertTrue(is_string($emails[0]));
    }

    public function testAddUser()
    {
        $username = '190' . mt_rand(12345678, 92345678);
        $originPassword = '123456';
        $password = UserAuth::createPassword($originPassword);
        $userInfo = [];
        $userInfo['password'] = UserAuth::createPassword($password);
        $userInfo['username'] = $username;
        $userInfo['display_name'] = $username;
        $userInfo['status'] = UserModel::STATUS_NORMAL;
        $userInfo['create_time'] = time();
        $userInfo['avatar'] = "";

        $userModel = new  UserModel();
        $ret = $userModel->addUser($userInfo);
        $this->assertEquals(UserModel::REG_RETURN_CODE_OK, $ret[0]);
        $this->assertNotEmpty($ret[1]);
        $userModel->deleteById($ret[1]['uid']);
    }

    public function testUpdateUserById()
    {
        $userModel = new  UserModel();
        list($ret, $msg) = $userModel->updateUserById('', '');
        $this->assertFalse($ret);

        list($ret, $msg) = $userModel->updateUserById([], self::$user['uid']);
        $this->assertFalse($ret);

        list($ret, $msg) = $userModel->updateUserById('', self::$user['uid']);
        $this->assertFalse($ret);

        $updateInfo['display_name'] = 'Funny';
        $updateInfo['status'] = UserModel::STATUS_NORMAL;
        $updateInfo['create_time'] = time() + 999;

        list($ret, $msg) = $userModel->updateUserById($updateInfo, self::$user['uid']);
        $this->assertTrue($ret);
        $this->assertEquals(1, intval($msg));
    }

    public function testUpdateUser()
    {
        $userModel = new  UserModel(self::$user['uid']);
        list($ret, $msg) = $userModel->updateUser([]);
        $this->assertFalse($ret);

        list($ret, $msg) = $userModel->updateUser('');
        $this->assertFalse($ret);

        $updateInfo['display_name'] = 'Funny2';
        $updateInfo['status'] = UserModel::STATUS_NORMAL;
        $updateInfo['create_time'] = time() + 999;

        list($ret, $msg) = $userModel->updateUser($updateInfo);
        $this->assertTrue($ret);
        $this->assertEquals(1, intval($msg));
    }
}