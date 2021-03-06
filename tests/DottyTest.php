<?php
/**
 * @author John Smart
 */

require 'bootstrap.php';

use dotty\Dotty;

class DottyTest extends \PHPUnit_Framework_TestCase
{
	public function testDot() {
		$data = array('name' => 'Scrooge McDuck');
		$found = Dotty::with($data)->one('name')->result();
		$this->assertEquals('Scrooge McDuck', $found);

		$data = array('Daffy Duck');
		$found = Dotty::with($data)->one('[0]')->result();
		$this->assertEquals('Daffy Duck', $found);

		$data = array(
			'piece' => array(
				'name' => 'Mozarts 9th',
				'measures' => array(
					array(
						'notes' => array(1, 2, 3),
						'intensity' => 10
					),
					array(
						'notes' => array(1, 4, 5),
						'intensity' => 7
					),
					array(
						'notes' => array(2, 3, 4, 5),
						'intensity' => 5
					)
				)
			)
		);

		$found = &Dotty::with($data)->one('piece.name')->result();
		$this->assertEquals($data['piece']['name'], $found);

		$found	= 'Radiohead';
		$this->assertEquals($found, $data['piece']['name']);

		$found = &Dotty::with($data)->one('piece.measures[1].notes')->result();
		$this->assertEquals($data['piece']['measures'][1]['notes'], $found);

		$found = &Dotty::with($data)->one('piece.measures[1].notes[2]')->result();
		$this->assertEquals($data['piece']['measures'][1]['notes'][2], $found);

		// test mutation
		$found = &Dotty::with($data)->one('piece.measures[1].notes[2]', $data)->result();
		$found	= 14;
		$this->assertEquals($found, $data['piece']['measures'][1]['notes'][2]);

		$dotty = Dotty::with($data)->first('foobar');
		$this->assertFalse($dotty->hasResult());
		$this->assertNull($dotty->result());

		$this->setExpectedException('\InvalidArgumentException');
		Dotty::with($data = array())->one('foobar', true);
	}

	public function testFirst() {
		$data = array(
			array(
				'notes' => array(1, 2, 3),
				'intensity' => 10
			),
			array(
				'notes' => array(1, 4, 5),
				'intensity' => 7
			),
			array(
				'notes' => array(2, 3, 4, 5),
				'intensity' => 5
			)
		);
		$forte	= &Dotty::with($data)->first('intensity')->result();
		$this->assertEquals(10, $forte);

		// test mutation
		$forte	= 11;
		$this->assertEquals(11, $data[0]['intensity']);

		$dotty = Dotty::with($data)->first('foobar');
		$this->assertFalse($dotty->hasResult());
		$this->assertNull($dotty->result());

		$this->setExpectedException('\InvalidArgumentException');
		Dotty::with($data)->first('foobar', true);
	}

	public function testAll() {
		$data = array(
			array(
				'notes' => array(1, 2, 3),
				'intensity' => 10
			),
			array(
				'notes' => array(1, 4, 5),
				'intensity' => 7
			),
			array(
				'notes' => array(2, 3, 4, 5),
				'intensity' => 5
			)
		);

		$set	= &Dotty::with($data)->all('intensity')->result();
		$this->assertEquals(array(10, 7, 5), $set);

		// test mutation
		$set[1]	= 10;
		$this->assertEquals(10, $data[1]['intensity']);
	}

	public function testSet() {
		$data = <<<DATA
{
   "data": [
      {
         "name": "Farenheit 451",
         "category": "Book",
         "id": "114836791862134",
         "created_time": "2010-09-30T19:36:29+0000"
      },
      {
         "name": "Coders at work",
         "category": "Book",
         "id": "104178566283955",
         "created_time": "2010-09-30T19:20:50+0000"
      },
      {
         "name": "Age of Spiritual Machines",
         "category": "Book",
         "id": "114114031939540",
         "created_time": "2010-09-30T19:20:50+0000"
      },
      {
         "name": "The Internets",
         "category": "Book",
         "id": "104303812934779",
         "created_time": "2010-05-03T19:36:18+0000"
      },
      {
         "name": "Ender's Game",
         "category": "Book",
         "id": "108178452537550",
         "created_time": "2010-05-03T19:36:18+0000"
      },
      {
         "name": "Catcher In The Rye",
         "category": "Book",
         "id": "115946728419025",
         "created_time": "2010-05-03T19:36:18+0000"
      },
      {
         "name": "George Orwell",
         "category": "Author",
         "id": "108663695825202",
         "created_time": "2010-05-03T19:36:18+0000"
      }
   ]
}
DATA;
		$data	= json_decode($data, true);
		$names	= Dotty::with($data)->all('name')->result();
		$this->assertEquals(array("Farenheit 451","Coders at work","Age of Spiritual Machines","The Internets","Ender's Game","Catcher In The Rye","George Orwell"), $names);
	}

	public function testEnsure() {
		$data	= array();
		Dotty::with($data)->ensure('level1');
		$this->assertArrayHasKey('level1', $data);

		$data	= array();
		Dotty::with($data)->ensure('level1.level2.level3');
		$this->assertArrayHasKey('level1', $data);
		$this->assertArrayHasKey('level2', $data['level1']);
		$this->assertArrayHasKey('level3', $data['level1']['level2']);
		$this->assertNull($data['level1']['level2']['level3']);

		$data	= array();
		Dotty::with($data)->ensure('level1.level2.level3[3]');
		$this->assertArrayHasKey('level1', $data);
		$this->assertArrayHasKey('level2', $data['level1']);
		$this->assertArrayHasKey('level3', $data['level1']['level2']);
		$this->assertArrayHasKey(3, $data['level1']['level2']['level3']);
		$this->assertNull($data['level1']['level2']['level3'][3]);

		$data	= array('cast' => array('path' => null));
		Dotty::with($data)->ensure('cast.path.for.me');
		$this->assertArrayHasKey('for', $data['cast']['path']);
		$this->assertInternalType('array', $data['cast']['path']['for']);
		$this->assertArrayHasKey('me', $data['cast']['path']['for']);

		$this->setExpectedException('\InvalidArgumentException');
		$data	= array('foo' => array('bar' => 'hello world'));
		Dotty::with($data)->ensure('foo.bar.me');
	}
}