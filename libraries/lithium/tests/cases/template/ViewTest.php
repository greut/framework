<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2010, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace lithium\tests\cases\template;

use lithium\template\View;
use lithium\action\Response;
use lithium\g11n\catalog\adapter\Memory;
use lithium\template\view\adapter\Simple;

class TestViewClass extends \lithium\template\View {

	public function renderer() {
		return $this->_config['renderer'];
	}
}

class ViewTest extends \lithium\test\Unit {

	protected $_view = null;

	public function setUp() {
		$this->_view = new View();
	}

	public function testInitialization() {
		$expected = new Simple();
		$this->_view = new TestViewClass(array('renderer' => $expected));
		$result = $this->_view->renderer();
		$this->assertEqual($expected, $result);
	}

	public function testInitializationWithBadLoader() {
		$this->expectException("Class 'Badness' of type 'adapter.template.view' not found.");
		new View(array('loader' => 'Badness'));
	}

	public function testInitializationWithBadRenderer() {
		$this->expectException("Class 'Badness' of type 'adapter.template.view' not found.");
		new View(array('renderer' => 'Badness'));
	}

	public function testEscapeOutputFilter() {
		$h = $this->_view->outputFilters['h'];
		$expected = '&lt;p&gt;Foo, Bar &amp; Baz&lt;/p&gt;';
		$result = $h('<p>Foo, Bar & Baz</p>');
		$this->assertEqual($expected, $result);
	}

	/**
	 * Tests that the output-escaping handler correctly inherits its encoding from the `Response`
	 * object, if provided.
	 *
	 * @return void
	 */
	public function testEscapeOutputFilterWithInjectedEncoding() {
		$message = "Multibyte string support must be enabled to test character encodings.";
		$this->skipIf(!function_exists('mb_convert_encoding'), $message);

		$string = "Joël";

		$response = new Response();
		$response->encoding = 'UTF-8';
		$view = new View(compact('response'));
		$handler = $view->outputFilters['h'];
		$this->assertTrue(mb_check_encoding($handler($string), "UTF-8"));

		$response = new Response();
		$response->encoding = 'ISO-8859-1';
		$view = new View(compact('response'));
		$handler = $view->outputFilters['h'];
		$this->assertTrue(mb_check_encoding($handler($string), "ISO-8859-1"));
	}

	public function testBasicRenderModes() {
		$view = new View(array('loader' => 'Simple', 'renderer' => 'Simple'));

		$result = $view->render('template', array('content' => 'world'), array(
			'template' => 'Hello {:content}!'
		));
		$expected = 'Hello world!';
		$this->assertEqual($expected, $result);

		$result = $view->render(array('element' => 'Logged in as: {:name}.'), array(
			'name' => "Cap'n Crunch"
		));
		$expected = "Logged in as: Cap'n Crunch.";
		$this->assertEqual($expected, $result);

		$result = $view->render('element', array('name' => "Cap'n Crunch"), array(
			'element' => 'Logged in as: {:name}.'
		));
		$expected = "Logged in as: Cap'n Crunch.";
		$this->assertEqual($expected, $result);

		$xmlHeader = '<' . '?xml version="1.0" ?' . '>' . "\n";
		$result = $view->render('all', array('type' => 'auth', 'success' => 'true'), array(
			'layout' => $xmlHeader . "\n{:content}\n",
			'template' => '<{:type}>{:success}</{:type}>'
		));
		$expected = "{$xmlHeader}\n<auth>true</auth>\n";
		$this->assertEqual($expected, $result);
	}

	public function testTwoStepRenderWithVariableCapture() {
		$view = new View(array('loader' => 'Simple', 'renderer' => 'Simple'));

		$result = $view->render(
			array(
				array('path' => 'element', 'capture' => array('data' => 'foo')),
				array('path' => 'template')
			),
			array('name' => "Cap'n Crunch"),
			array('element' => 'Logged in as: {:name}.', 'template' => '--{:foo}--')
		);
		$this->assertEqual('--Logged in as: Cap\'n Crunch.--', $result);
	}

	public function testFullRenderNoLayout() {
		$view = new View(array('loader' => 'Simple', 'renderer' => 'Simple'));
		$result = $view->render('all', array('type' => 'auth', 'success' => 'true'), array(
			'template' => '<{:type}>{:success}</{:type}>'
		));
		$expected = '<auth>true</auth>';
		$this->assertEqual($expected, $result);
	}
}

?>