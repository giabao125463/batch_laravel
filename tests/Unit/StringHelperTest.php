<?php

namespace Tests\Unit\Traits;

use App\Helpers\StringHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StringHelperTest extends TestCase
{
    public function subStringProivder()
    {
        return [
            ['１２３４５６７８９０1234567890123456789あ', '１２３４５６７８９０1234567890123456789'],
            ['食品テスト', '食品テスト'],
            ['０１２３４５６７８９０１２３４５６７８９０', '０１２３４５６７８９０１２３４５６７８９'],
            ['マリーンズSGゲルマパワーバランスXαシリコーンループ－BK', 'マリーンズSGゲルマパワーバランスXαシリ'],
            ['１２３４５６７８９０12345678901234567890', '１２３４５６７８９０12345678901234567890'],
            ['花火フェイスタオル2019', '花火フェイスタオル2019'],
            ['ガーゼハンカチ　マーくん', 'ガーゼハンカチ　マーくん'],
            ['マリーンズSGゲルマパワーバランスXαシリコーンループ－WH', 'マリーンズSGゲルマパワーバランスXαシリ'],
            ['マリーンズSGゲルマパワーバランスXαシリコーンブレス－BK', 'マリーンズSGゲルマパワーバランスXαシリ'],
            ['マリーンズSGゲルマパワーバランスXαシリコーンブレス－WH', 'マリーンズSGゲルマパワーバランスXαシリ'],
            ['123456789012345678901234567890123456789あ', '123456789012345678901234567890123456789'],
            ['０１２３４５６７８９０１２３４５６７８9', '０１２３４５６７８９０１２３４５６７８9'],
            ['1234567890123456789012345678901234567890', '1234567890123456789012345678901234567890'],
            ['1234567890', '1234567890'],
            ['#12石川マリンフェスタスタメンユニフォームメモラビリア＜2019/4/21＞<br>※先着1名...', '#12石川マリンフェスタスタメンユニフォー'],
            ['￥竭?/竭。', '￥竭?/竭。'],
            ['BIGSEIYA　フェイスタオル', 'BIGSEIYA　フェイスタオル'],
            ['BIGロゴフェイスタオル　ブラック', 'BIGロゴフェイスタオル　ブラック'],
            ['Marinesゴルフボール６P　LMBA-7756', 'Marinesゴルフボール６P　LMBA-7756'],
            ['MARINES歯ブラシ', 'MARINES歯ブラシ'],
            ['いだてん荻野フェイスタオル', 'いだてん荻野フェイスタオル'],
            ['ガーゼハンカチ　リーンちゃん', 'ガーゼハンカチ　リーンちゃん'],
            ['ガーゼハンカチ　謎の魚第３形態', 'ガーゼハンカチ　謎の魚第３形態'],
            ['クリスタルボール　パールネックレス', 'クリスタルボール　パールネックレス'],
            ['ジェットストリーム　　ＳＸＮ-１０００-０７', 'ジェットストリーム　　ＳＸＮ-１０００-０'],
            ['ズーちゃんフェイスタオル', 'ズーちゃんフェイスタオル'],
            ['ピンクストライプフェイスタオル', 'ピンクストライプフェイスタオル'],
            ['ホームをねらえ！岡大海フェイスタオル', 'ホームをねらえ！岡大海フェイスタオル'],
            ['マーくんフェイスタオル', 'マーくんフェイスタオル'],
            ['マリーンズロゴクリスタルペンダント（メンズ）', 'マリーンズロゴクリスタルペンダント（メン'],
            ['リーンちゃんフェイスタオル', 'リーンちゃんフェイスタオル'],
            ['半袖プリントTシャツ_A', '半袖プリントTシャツ_A'],
            ['大勝セミバスタオル', '大勝セミバスタオル'],
        ];
    }

    /**
     * testSubString
     *
     * @dataProvider subStringProivder
     * @group stringHelper
     * @return void
     */
    public function testSubString($input, $expect)
    {
        $actual = StringHelper::subString($input, 40);
        $this->assertEquals($expect, $actual);
    }

    public function removeWhiteSpaceProivder()
    {
        return [
            ['ガーゼハンカチ　マーくん', true, 'ガーゼハンカチマーくん'],
            ['ガーゼハンカチ　マーくん', false, 'ガーゼハンカチ　マーくん'],
            ['マリーンズ SGゲ', true, 'マリーンズSGゲ'],
            ['マリーンズ SGゲ', false, 'マリーンズSGゲ'],
            [' 1 2 3 4 5 6 　', true, '123456'],
            [' 1 2 3 4 5 6 　', false, '123456　'],
            ['　1　2　3　4　5　6　', true, '123456'],
            ['　1　2　3　4　5　6　', false, '　1　2　3　4　5　6　'],

        ];
    }

    /**
     * Test remove white space
     *
     * @dataProvider removeWhiteSpaceProivder
     * @group stringHelper
     * @return void
     */
    public function testRemoveWhiteSpace($input, $mbSpace, $expect)
    {
        $actual = StringHelper::removeWhiteSpace($input, $mbSpace);
        $this->assertEquals($expect, $actual);
    }
}
