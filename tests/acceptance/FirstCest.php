<?php

class FirstCest
{
    public function test(AcceptanceTester $I)
    {
        //login in
        $I->amOnPage('/');
       
        $I->see('Sign in');
        $I->click('Sign in');
        
        $I->fillField(['id' => 'login_main_login'], 'istomin_84@mail.ru');        
        $I->fillField(['id' => 'psw_main_login'], 'maricas12');        
        $I->click('form[name=main_login_form] button[type=submit]');        
        
        //check units
        $I->see('my account');
        $I->click('My Account');        
        $I->Click('Отделы');
        $I->see('second unit');
        $I->see('Руководитель');
        $I->see('Customer');
        $I->see('Вах');
        //check unit
        $I->Click('second unit');
        $I->see('First name');
        $I->see('Customer');
        $I->see('customer@example.com');
        // log out
        $I->Click('My Account');
        $I->Click('Sign out');
        //check if really logged out
        $I->Click('My Account');
        $I->see('Sign in');
        //result
        $I-> makeHtmlSnapshot();

        

    }

    // tests
    public function tryToTest(AcceptanceTester $I)
    {
    }
}
