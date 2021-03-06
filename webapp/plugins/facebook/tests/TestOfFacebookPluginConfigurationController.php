<?php
if ( !isset($RUNNING_ALL_TESTS) || !$RUNNING_ALL_TESTS ) {
    require_once '../../../../tests/config.tests.inc.php';
}
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/simpletest/autorun.php';

require_once THINKUP_ROOT_PATH.'webapp/plugins/facebook/tests/classes/mock.facebook.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/facebook/model/class.FacebookPlugin.php';
require_once THINKUP_ROOT_PATH.'webapp/plugins/facebook/controller/class.FacebookPluginConfigurationController.php';
require_once THINKUP_ROOT_PATH.'webapp/_lib/extlib/facebook/facebook.php';

/**
 * Test of FacebookPluginConfigurationController
 *
 * @author Gina Trapani <ginatrapani[at]gmail[dot]com>
 *
 */
class TestOfFacebookPluginConfigurationController extends ThinkUpUnitTestCase {

    /**
     * Constructor
     */
    public function __construct() {
        $this->UnitTestCase('FacebookPluginConfigurationController class test');
    }

    /**
     * Setup
     */
    public function setUp(){
        parent::setUp();
        $webapp = Webapp::getInstance();
        $webapp->registerPlugin('twitter', 'TwitterPlugin');

        //Add owner
        $q = "INSERT INTO tu_owners SET id=1, full_name='ThinkUp J. User', email='me@example.com', is_activated=1,
        pwd='XXX', activation_code='8888'";
        $this->db->exec($q);

        //Add instance_owner
        $q = "INSERT INTO tu_owner_instances (owner_id, instance_id) VALUES (1, 1)";
        $this->db->exec($q);

        //Insert test data into test table
        $q = "INSERT INTO tu_users (user_id, user_name, full_name, avatar, last_updated) VALUES (13, 'ev',
        'Ev Williams', 'avatar.jpg', '1/1/2005');";
        $this->db->exec($q);

        //Make public
        $q = "INSERT INTO tu_instances (id, network_user_id, network_username, is_public) VALUES (1, 13, 'ev', 1);";
        $this->db->exec($q);

        //Add a bunch of posts
        $counter = 0;
        while ($counter < 40) {
            $pseudo_minute = str_pad($counter, 2, "0", STR_PAD_LEFT);
            $q = "INSERT INTO tu_posts (post_id, author_user_id, author_username, author_fullname, author_avatar,
            post_text, source, pub_date, reply_count_cache, retweet_count_cache) VALUES ($counter, 13, 'ev', 
            'Ev Williams', 'avatar.jpg', 'This is post $counter', 'web', '2006-01-01 00:$pseudo_minute:00', ".
            rand(0, 4).", 5);";
            $this->db->exec($q);
            $counter++;
        }
    }

    /**
     * Test constructor
     */
    public function testConstructor() {
        $controller = new FacebookPluginConfigurationController(null);
        $this->assertTrue(isset($controller), 'constructor test');
    }

    public function testConfigNotSet() {
        $plugin_options_dao = DAOFactory::getDAO("PluginOptionDAO");
        PluginOptionMySQLDAO::$cached_options = array();
        $_SESSION['user'] = 'me@example.com';
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail($_SESSION['user']);
        $controller = new FacebookPluginConfigurationController($owner);
        $results = $controller->go();

        $v_mgr = $controller->getViewManager();
        //@TODO Figure out why API keys are not set here in the test, but they are in the controller
        $this->assertEqual($v_mgr->getTemplateDataItem('errormsg'),
        'Please set your Facebook API Key and Application Secret.');
    }

    /**
     * Test output
     */
    public function testOutputNoParams() {
        //not logged in, no owner set
        $builders = $this->buildPluginOptions();
        $controller = new FacebookPluginConfigurationController(null);
        $output = $controller->go();
        $v_mgr = $controller->getViewManager();
        $config = Config::getInstance();
        $this->assertEqual('You must <a href="'.$config->getValue('site_root_path').
            'session/login.php">log in</a> to do this.', $v_mgr->getTemplateDataItem('errormsg'));

        //logged in
        $_SESSION['user'] = 'me@example.com';
        $owner_dao = DAOFactory::getDAO('OwnerDAO');
        $owner = $owner_dao->getByEmail($_SESSION['user']);
        $controller = new FacebookPluginConfigurationController($owner);
        $output = $controller->go();
        $v_mgr = $controller->getViewManager();
        $this->assertIsA($v_mgr->getTemplateDataItem('owner_instances'), 'array', 'Owner instances set');
        $this->assertTrue($v_mgr->getTemplateDataItem('fbconnect_link') != '', 'Authorization link set');
    }

    /**
     * build plugin option values
     */
    private function buildPluginOptions() {
        $plugin1 = FixtureBuilder::build('plugins', array('id'=>2, 'folder_name'=>'facebook'));
        $plugin_opt1 = FixtureBuilder::build('plugin_options',
        array('plugin_id' => 2, 'option_name' => 'facebook_api_key', 'option_value' => "dummy_key") );
        $plugin_opt2 = FixtureBuilder::build('plugin_options',
        array('plugin_id' => 2, 'option_name' => 'facebook_api_secret', 'option_value' => "dummy_secret") );
        return array($plugin_opt1, $plugin_opt2, $plugin1);
    }
}
