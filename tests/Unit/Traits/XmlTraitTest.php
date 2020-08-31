<?php

namespace Tests\Unit\Traits;

use App\Traits\XmlTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class XmlTraitTest extends TestCase
{
    use XmlTrait;

    public function fillWidthWithSpaceProvider()
    {
        return [
            ['マリーンズSGゲルマパワーバランスXαシリコーンループ－BK', 'マリーンズSGゲルマパワーバランスXαシリ '],
            ['マリーンズSGゲルマパワーバランスXαシリコーンループ－WH', 'マリーンズSGゲルマパワーバランスXαシリ '],
            ['マリーンズSGゲルマパワーバランスXαシリコーンブレス－BK', 'マリーンズSGゲルマパワーバランスXαシリ '],
            ['マリーンズSGゲルマパワーバランスXαシリコーンブレス－WH', 'マリーンズSGゲルマパワーバランスXαシリ '],
            ['123456789012345678901234567890123456789あ', '123456789012345678901234567890123456789 '],
            ['０１２３４５６７８９０１２３４５６７８９０', '０１２３４５６７８９０１２３４５６７８９'],
            ['０１２３４５６７８９０１２３４５６７８9', '０１２３４５６７８９０１２３４５６７８9 '],
            ['１２３４５６７８９０12345678901234567890', '１２３４５６７８９０12345678901234567890'],
            ['１２３４５６７８９０1234567890123456789あ', '１２３４５６７８９０1234567890123456789 '],
            ['1234567890123456789012345678901234567890', '1234567890123456789012345678901234567890'],
            ['1234567890', '1234567890                              '],
            ['#12石川 マリンフェスタスタメンユニフォームメモラビリア ＜2019/4/21＞<br>※先着1名...', '#12石川 マリンフェスタスタメンユニフォー'],
            ['＜福浦選手引退記念グッズ＞<br>福浦引退記念バスタオルBK', '＜福浦選手引退記念グッズ＞br福浦引退記念'],
            ['＜福浦選手引退記念グッズ＞<br>福浦引退記念バスタオルWH', '＜福浦選手引退記念グッズ＞br福浦引退記念'],
            ['＜福浦選手引退記念グッズ＞<br>福浦引退記念バスタオル球団旗', '＜福浦選手引退記念グッズ＞br福浦引退記念'],
            ['＜福浦選手引退記念グッズ＞<br>福浦引退記念フェイスタオル応援歌', '＜福浦選手引退記念グッズ＞br福浦引退記念'],
            ['＜福浦選手引退記念グッズ＞<br>福浦引退記念フェイスタオル球団旗', '＜福浦選手引退記念グッズ＞br福浦引退記念'],
            ['＜福浦選手引退記念グッズ＞<br>福浦引退記念マフラータオル', '＜福浦選手引退記念グッズ＞br福浦引退記念'],
            ['￥竭?/竭。', '￥竭?/竭。                              '],
            ['BIG SEIYA　フェイスタオル', 'BIG SEIYA　フェイスタオル               '],
            ['BIGロゴフェイスタオル　ブラック', 'BIGロゴフェイスタオル　ブラック         '],
            ['Marines ゴルフボール６P　LMBA-7756', 'Marines ゴルフボール６P　LMBA-7756      '],
            ['MARINES 歯ブラシ', 'MARINES 歯ブラシ                        '],
            ['いだてん荻野フェイスタオル', 'いだてん荻野フェイスタオル              '],
            ['ガーゼハンカチ　マーくん', 'ガーゼハンカチ　マーくん                '],
            ['ガーゼハンカチ　リーンちゃん', 'ガーゼハンカチ　リーンちゃん            '],
            ['ガーゼハンカチ　謎の魚第３形態', 'ガーゼハンカチ　謎の魚第３形態          '],
            ['クリスタルボール　パールネックレス', 'クリスタルボール　パールネックレス      '],
            ['ジェットストリーム　　ＳＸＮ-１０００-０７', 'ジェットストリーム　　ＳＸＮ-１０００-０'],
            ['ズーちゃんフェイスタオル', 'ズーちゃんフェイスタオル                '],
            ['ピンクストライプフェイスタオル', 'ピンクストライプフェイスタオル          '],
            ['ホームをねらえ！岡大海フェイスタオル', 'ホームをねらえ！岡大海フェイスタオル    '],
            ['マーくんフェイスタオル', 'マーくんフェイスタオル                  '],
            ['マリーンズロゴクリスタルペンダント（メンズ）', 'マリーンズロゴクリスタルペンダント（メン'],
            ['リーンちゃんフェイスタオル', 'リーンちゃんフェイスタオル              '],
            ['半袖プリントTシャツ_A', '半袖プリントTシャツ_A                   '],
            ['大勝セミバスタオル', '大勝セミバスタオル                      '],
            ['花火フェイスタオル2019', '花火フェイスタオル2019                  '],
            ['食品テスト', '食品テスト                              '],
        ];
    }

    /**
     * TestFillWidthWithSpace
     *
     * @dataProvider fillWidthWithSpaceProvider
     * @group xml
     * @return void
     */
    public function testFillWidthWithSpace($input, $expect)
    {
        $actual = $this->fillWidthWithSpace($input, 40);
        $this->assertEquals($expect, $actual);
    }

    public function removeSpecialCharactersProvider()
    {

        return [
            ['1234<>?"', '1234?'],
            ['1234<>?"4321', '1234?4321'],
            ['<1234>&"\'4321', '12344321'],
            ['<1234>&"""""""4321""""', '12344321'],
            ['<123\'\'\'4>&&&&&&"""""""4321""""', '12344321'],
            ['<食品テスト>', '食品テスト'],
            ['<食品テ%スト>', '食品テ%スト'],
            ['<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<', ''],
        ];
    }

    /**
     * TestFillWidthWithSpace
     *
     * @dataProvider removeSpecialCharactersProvider
     * @group xml
     * @return void
     */
    public function testRemoveSpecialCharacters($input, $expect)
    {
        $actual = $this->removeSpecialCharacters($input);
        $this->assertEquals($expect, $actual);
    }
}