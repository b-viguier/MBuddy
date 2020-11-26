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

        $id1 = $bank->save('MyName1', $data1 = 'my-data1');
        $id2 = $bank->save('MyName2', $data2 = 'my-data2');

        assert($id1 !== null && $id2 !== null);
        $this->assertSame(0, $id1);
        $this->assertSame($data1, $bank->load($id1));
        $this->assertSame(1, $id2);
        $this->assertSame($data2, $bank->load($id2));
    }

    public function testUpdateDataByName(): void
    {
        $folder = new TestUtils\TempFolder(__METHOD__);
        assert($folder->directory()->getRealPath() !== false);

        $bank = new MBuddy\MidiSyxBank($folder->directory()->getRealPath());
        $this->assertNull($bank->load(0));

        $id1 = $bank->save($name = 'MyName1', $data1 = 'my-data1');
        assert($id1 !== null);
        $this->assertSame(0, $id1);
        $this->assertSame($data1, $bank->load($id1));

        $id2 = $bank->save($name, $data2 = 'my-data2');
        assert($id2 !== null);
        $this->assertSame($id1, $id2);
        $this->assertSame($data2, $bank->load($id2));
    }
}
