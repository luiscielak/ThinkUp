<?php
require_once dirname(__FILE__).'/init.tests.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/web_tester.php';

class TestOfChangePassword extends ThinkUpWebTestCase {

    public function setUp() {
        parent::setUp();

        //Add owner
        $session = new Session();
        $cryptpass = $session->pwdcrypt("secretpassword");
        $q = "INSERT INTO tu_owners (id, email, pwd, is_activated) VALUES (1, 'me@example.com', '".$cryptpass."', 1)";
        $this->db->exec($q);

        //Add instance
        $q = "INSERT INTO tu_instances (id, network_user_id, network_username, is_public) VALUES (1, 1234, 'thinkupapp',
         1)";
        $this->db->exec($q);

        //Add instance_owner
        $q = "INSERT INTO tu_owner_instances (owner_id, instance_id) VALUES (1, 1)";
        $this->db->exec($q);
    }

    public function tearDown() {
        parent::tearDown();
    }


    public function testChangePasswordSuccess() {
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'me@example.com');
        $this->setField('pwd', 'secretpassword');

        $this->click("Log In");
        $this->assertTitle('Private Dashboard | ThinkUp');
        $this->assertText('Logged in as: me@example.com');

        $this->click("Configuration");
        $this->assertText('Your ThinkUp Password');
        $this->setField('oldpass', 'secretpassword');
        $this->setField('pass1', 'secretpassword1');
        $this->setField('pass2', 'secretpassword1');
        $this->click('Change password');
        $this->assertText('Your password has been updated.');

        $this->click("Log Out");
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'me@example.com');
        $this->setField('pwd', 'secretpassword1');

        $this->click("Log In");
        $this->assertTitle('Private Dashboard | ThinkUp');
        $this->assertText('Logged in as: me@example.com');
    }

    public function testChangePasswordWrongExistingPassword() {
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'me@example.com');
        $this->setField('pwd', 'secretpassword');

        $this->click("Log In");
        $this->assertTitle('Private Dashboard | ThinkUp');
        $this->assertText('Logged in as: me@example.com');

        $this->click("Configuration");
        $this->assertText('Your ThinkUp Password');
        $this->setField('oldpass', 'secretpassworddd');
        $this->setField('pass1', 'secretpassword1');
        $this->setField('pass2', 'secretpassword1');
        $this->click('Change password');
        $this->assertText('Old password does not match or empty.');
    }

    public function testChangePasswordEmptyExistingPassword() {
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'me@example.com');
        $this->setField('pwd', 'secretpassword');

        $this->click("Log In");
        $this->assertTitle('Private Dashboard | ThinkUp');
        $this->assertText('Logged in as: me@example.com');

        $this->click("Configuration");
        $this->assertText('Your ThinkUp Password');
        $this->setField('pass1', 'secretpassword1');
        $this->setField('pass2', 'secretpassword1');
        $this->click('Change password');
        $this->assertText('Old password does not match or empty.');
    }

    public function testChangePasswordNewPasswordsDontMatch() {
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'me@example.com');
        $this->setField('pwd', 'secretpassword');

        $this->click("Log In");
        $this->assertTitle('Private Dashboard | ThinkUp');
        $this->assertText('Logged in as: me@example.com');

        $this->click("Configuration");
        $this->assertText('Your ThinkUp Password');
        $this->setField('oldpass', 'secretpassword');
        $this->setField('pass1', 'secretpassword1');
        $this->setField('pass2', 'secretpassword2');
        $this->click('Change password');
        $this->assertText('New passwords did not match. Your password has not been changed.');
    }

    public function testChangePasswordNewPasswordsNotLongEnough() {
        $this->get($this->url.'/session/login.php');
        $this->setField('email', 'me@example.com');
        $this->setField('pwd', 'secretpassword');

        $this->click("Log In");
        $this->assertTitle('Private Dashboard | ThinkUp');
        $this->assertText('Logged in as: me@example.com');

        $this->click("Configuration");
        $this->assertText('Your ThinkUp Password');
        $this->setField('oldpass', 'secretpassword');
        $this->setField('pass1', 'dd');
        $this->setField('pass2', 'dd');
        $this->click('Change password');
        $this->assertText('New password must be at least 5 characters. Your password has not been changed.');
    }
}