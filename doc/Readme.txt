h3. {info}The extension "container" enables Dependency Injection in TYPO3 4.3. It has a subset of the FLOW3 features. {info}

Usage

Instead of calling new or makeInstance you should call a container to give you a new object. The container is aware of building the object with the dependencies:

You need a container if you need to create new objects (e.g. in a factory), normally very few classes needs to do this.

If your class needs a container you should inject them (see below).

For initial startup you can request a container with:

{code}
Tx_Container_Container::getContainer()
{code}

h3. Constructor Injection

You just have to write your constructor and you will get the dependencys:

{code}
 class tx_container_tests_amixed_string {
     public function __construct(tx_container_tests_b $b, tx_container_tests_c $c, $myvalue='test') {
         
     }    
 }
{code}
Use this to get Dependencys that are required for your class to do the job.


h3. Setter Injection

Can be used by annotation "@inject" or by naming a method "inject*":



{code}
class tx_container_tests_injectmethods {
    public $b;
    public $bchild;
    
    public function injectClassB(tx_container_tests_b $o) {
        $this->b = $o;
    }
    
    /**
     * @inject
     * @param tx_container_tests_b $o
     */
    public function setClassBChild(tx_container_tests_b_child $o) {
        $this->bchild = $o;
    }
}
{code}

h3. Settings Injection

You can also inject the ext_conf_template Settings by simple using a method "injectExtensionSettings":

{code}
 public function injectExtensionSettings(array $settings) {
        $this->settings = $settings;
    }
{code}

h3. Singletons

Per default all objects are prototype, if you want them to be singletons, just implememt&nbsp; t3lib_Singleton
{code}
 class tx_container_tests_singleton implements t3lib_Singleton {
    
}
{code}

h3. Interfaces and Abstract Classes

The container is not aware of the available implementations of and interface or abstract class. Therefore you need to let him know in the ext_localconf.php:

Tx_Container_Container::getContainer()->registerImplementation('tx_container_tests_someinterface', 'tx_container_tests_someimplementation');&nbsp;&nbsp; &nbsp;&nbsp;&nbsp; 