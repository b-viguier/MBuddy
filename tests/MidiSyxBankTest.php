<?php

namespace bviguier\tests\MBuddy;

use PHPUnit\Framework\TestCase;
use bviguier\tests\MBuddy\TestUtils;
use bviguier\MBuddy;

class MidiSyxBankTest extends TestCase
{
    public function testEmptyBank(): void
    {
        $folder = new TestUtils\TempFolder(__METHOD__);
        assert($folder->directory()->getRealPath() !== false);

        $bank = new MBuddy\MidiSyxBank($folder->directory()->getRealPath());

        $this->assertNull($bank->load(0));
        $this->assertNull($bank->load(127));
    }

    public function testSaveAndLoadData(): void
    {
        $folder = new TestUtils\TempFolder(__METHOD__);
        assert($folder->directory()->getRealPath() !== false);

        $bank = new MBuddy\MidiSyxBank($folder->directory()->getRealPath());
        $this->assertNull($bank->load(0));

        $this->assertTrue($bank->save($id1 = 0,'MyName1', $data1 = 'my-data1'));
        $this->assertTrue($bank->save($id2 = 1, 'MyName2', $data2 = 'my-data2'));

        $this->assertSame($data1, $bank->load($id1));
        $this->assertSame($data2, $bank->load($id2));
    }

    public function testUpdateDataWithSameId(): void
    {
        $folder = new TestUtils\TempFolder(__METHOD__);
        assert($folder->directory()->getRealPath() !== false);

        $bank = new MBuddy\MidiSyxBank($folder->directory()->getRealPath());
        $this->assertNull($bank->load(0));

        $this->assertTrue($bank->save($id = 0, $name = 'MyName1', $data1 = 'my-data1'));
        $this->assertSame($data1, $bank->load($id));

        $this->assertTrue($bank->save($id, $name, $data2 = 'my-data2'));
        $this->assertSame($data2, $bank->load($id));
    }
}
