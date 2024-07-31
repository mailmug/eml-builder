<?php declare(strict_types=1);

use Mailmug\EmlBuilder\EmlBuilder;
use PHPUnit\Framework\TestCase;

final class EmlBuilderTest extends TestCase
{
    public function testToEmailAddress(): void
    {
        $toEmail = '"Foo" <foo@example.com>, "Bar" <bar@example.com>';
        $fromEmail = "no-reply@bar.com";
        $ccEmail = '"Foo Bar" <foo@bar.com>,  <info@bar.com>';
        $bccEmail = '"Foo Bar" <foo@bar.com>';

        $emlBuilder = new EmlBuilder;

        $data = '{
            "from": "no-reply@bar.com",
            "to": [ 
              { "name": "Foo", "email": "foo@example.com" },
              { "name": "Bar", "email": "bar@example.com" }
            ],
            "cc": [
              { "name": "Foo Bar", "email": "foo@bar.com" },
              { "email": "info@bar.com" }
            ],
            "bcc": { "name": "Foo Bar", "email": "foo@bar.com" },
            "subject": "Winter promotions"
        }';

        $data = json_decode( $data );

        $this->assertSame($fromEmail, $emlBuilder::toEmailAddress( $data->from ));
        $this->assertSame($toEmail, $emlBuilder::toEmailAddress( $data->to ));
        $this->assertSame($ccEmail, $emlBuilder::toEmailAddress( $data->cc ));
        $this->assertSame($bccEmail, $emlBuilder::toEmailAddress( $data->bcc ));
        $this->assertSame($bccEmail, $emlBuilder::toEmailAddress( '"Foo Bar" <foo@bar.com>', '=?UTF-8?Q?You=E2=80=99re=20Foo=20Bar?= <foo@bar.com>' ));
    }

    public function testBoundary(): void{
        $emlBuilder = new EmlBuilder;
        $string = "multipart/related; type=\"text/html\";\r\nboundary=\"b1_4afb675bba4c412783638afbee8e8c71\"";
        $this->assertSame("b1_4afb675bba4c412783638afbee8e8c71", $emlBuilder::getBoundary( $string ));
    }
}