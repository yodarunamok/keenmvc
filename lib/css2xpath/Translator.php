<?php
/**
 * Created by IntelliJ IDEA.
 * User: Chris Hansen (chris@iviking.org)
 * Date: 4/29/21
 * Time: 12:46 PM
 */

namespace KeenMVC\CSS2XPath;

/**
 * Class Translator
 */
class Translator {

    /** @var array */
    private $rules;

    /**
     * @param   string  $selector
     * @return  string
     */
    public function translate($selector) {
        foreach ($this->getRules() as $rule) {
            /** @var RuleInterface $rule */
            $selector = $rule->apply($selector);
        }

        return $selector === '/' ? '/' : '//' . $selector;
    }

    /**
     * @return  array
     */
    private function getRules() {
        if ($this->rules !== null) {
            return $this->rules;
        }

        $this->rules = [

            // prefix|name
            new RegexRule('/([a-zA-Z0-9\_\-\*]+)\|([a-zA-Z0-9\_\-\*]+)/', '$1:$2'),

            // add @ for attribs
            new RegexRule("/\[([^G\]~\$\*\^\|\!]+)(=[^\]]+)?\]/", '[@$1$2]'),

            // multiple queries
            new RegexRule("/\s*,\s*/", '|'),

            // , + ~ >
            new RegexRule("/\s*([\+~>])\s*/", '$1'),

            //* ~ + >
            new RegexRule("/([a-zA-Z0-9\_\-\*])~([a-zA-Z0-9\_\-\*])/", '$1/following-sibling::$2'),
            new RegexRule("/([a-zA-Z0-9\_\-\*])\+([a-zA-Z0-9\_\-\*])/", '$1/following-sibling::*[1]/self::$2'),
            new RegexRule("/([a-zA-Z0-9\_\-\*])>([a-zA-Z0-9\_\-\*])/", '$1/$2'),

            // all unescaped stuff escaped
            new RegexRule("/\[([^=]+)=([^'|][^\]]*)\]/", '[$1="$2"]'),

            // all descendant or self to //
            new RegexRule("/(^|[^a-zA-Z0-9\_\-\*])([#\.])([a-zA-Z0-9\_\-]+)/", '$1*$2$3'),
            new RegexRule("/([\>\+\|\~\,\s])([a-zA-Z\*]+)/", '$1//$2'),
            new RegexRule("/\s+\/\//", '//'),

            // :first-child
            new RegexRule("/([a-zA-Z0-9\_\-\*]+):first-child/", '*[1]/self::$1'),

            // :last-child
            new RegexRule("/([a-zA-Z0-9\_\-\*]+)?:last-child/", '$1[not(following-sibling::*)]'),

            // :only-child
            new RegexRule("/([a-zA-Z0-9\_\-\*]+):only-child/", '*[last()=1]/self::$1'),

            // :empty
            new RegexRule("/([a-zA-Z0-9\_\-\*]+)?:empty/", '$1[not(*) and not(normalize-space())]'),

            // :not
            new NotRule($this),

            // :nth-child
            new NthChildRule(),

            // :contains(selectors)
            new RegexRule('/:contains\(([^\)]*)\)/', '[contains(string(.),"$1")]'),

            // |= attrib
            new RegexRule("/\[([a-zA-Z0-9\_\-]+)\|=([^\]]+)\]/", '[@$1=$2 or starts-with(@$1,concat($2,"-"))]'),

            // *= attrib
            new RegexRule("/\[([a-zA-Z0-9\_\-]+)\*=([^\]]+)\]/", '[contains(@$1,$2)]'),

            // ~= attrib
            new RegexRule("/\[([a-zA-Z0-9\_\-]+)~=([^\]]+)\]/", '[contains(concat(" ",normalize-space(@$1)," "),concat(" ",$2," "))]'),

            // ^= attrib
            new RegexRule("/\[([a-zA-Z0-9\_\-]+)\^=([^\]]+)\]/", '[starts-with(@$1,$2)]'),

            // $= attrib
            new DollarEqualRule(),

            // != attrib
            new RegexRule("/\[([a-zA-Z0-9\_\-]+)\!=[\"']+?([^\"\]]+)[\"']+?\]/", '[not(@$1) or @$1!="$2"]'),

            // ids
            new RegexRule("/#([a-zA-Z0-9\_\-]+)/", '[@id="$1"]'),

            // classes
            new RegexRule("/\.([a-zA-Z0-9_-]+)(?![^[]*])/", '[contains(concat(" ",normalize-space(@class)," ")," $1 ")]'),

            // normalize multiple filters
            new RegexRule("/\]\[([^\]]+)/", ' and ($1)'),

            // tag:pseudo selectors
            new RegexRule('/(:enabled)/', '[not(@disabled)]'),
            new RegexRule('/(:checked)/', '[@checked="checked"]'),
            new RegexRule('/:(disabled)/', '[@$1]'),
            new RegexRule('/:root/', '/'),

            // use * when tag was omitted
            new RegexRule("/^\[/", '*['),
            new RegexRule("/\|\[/", '|*[')
        ];

        return $this->rules;
    }
}

class DollarEqualRule implements RuleInterface {
    /**
     * @param   string  $selector
     * @return  string
     */
    public function apply($selector) {
        return preg_replace_callback(
            '/\[([a-zA-Z0-9\_\-]+)\$=([^\]]+)\]/',
            [$this, 'callback'],
            $selector
        );
    }

    /**
     * Build query from matches.
     * @param   array   $matches
     * @return  string
     */
    private function callback($matches) {
        return '[substring(@' . $matches[1] . ',string-length(@' . $matches[1] . ')-' . (strlen($matches[2]) - 3) . ')=' . $matches[1] . ']';
    }
}

class NotRule implements RuleInterface {

    /** @var Translator */
    private $translator;

    public function __construct(Translator $translator) {
        $this->translator = $translator;
    }

    /**
     * @param   string  $selector
     * @return  string
     */
    public function apply($selector) {
        return preg_replace_callback(
            '/([a-zA-Z0-9\_\-\*]+):not\(([^\)]*)\)/',
            [$this, 'callback'],
            $selector
        );
    }

    /**
     * @param   array   $matches
     * @return  string
     */
    private function callback($matches) {
        $subResult = preg_replace(
            '/^[^\[]+\[([^\]]*)\].*$/',
            '$1',
            $this->translator->translate($matches[2])
        );

        return $matches[1] . '[not(' . $subResult . ')]';
    }
}

class NthChildRule implements RuleInterface {
    /**
     * @param   string  $selector
     * @return  string
     */
    public function apply($selector) {
        return preg_replace_callback(
            '/([a-zA-Z0-9_\-*]+):nth-child\(([^)]*)\)/',
            [$this, 'callback'],
            $selector
        );
    }

    /**
     * @param   array   $matches
     * @return  string
     */
    private function callback($matches) {
        switch ($matches[2]) {
            case 'n': {  // :nth-child(n)
                return $matches[1];
            }
            case 'even': { // :nth-child(even)
                return sprintf('%s[(count(preceding-sibling::*) + 1) mod 2=0]', $matches[1]);
            }
            case 'odd': { // :nth-child(odd)
                return $matches[1] . '[(count(preceding-sibling::*) + 1) mod 2=1]';
            }
            case preg_match('/^\d*$/', $matches[2]) === 1: { // :nth-child(1)
                return sprintf('*[%d]/self::%s', $matches[2], $matches[1]);
            }
            default: { // :nth-child(1n+2)
                $b = preg_replace('/^([\d]*)n.*?([\d]*)$/', '$1+$2', $matches[2]);
                $b = explode('+', $b);

                return sprintf(
                    '%s[(count(preceding-sibling::*)+1)>=%d and ((count(preceding-sibling::*)+1)-%d) mod %d=0]',
                    $matches[1],
                    $b[1],
                    $b[1],
                    $b[0]
                );
            }
        }
    }
}

class RegexRule implements RuleInterface {

    /** @var string */
    private $regex;

    /** @var string */
    private $replacement;

    /**
     * RegexRule constructor.
     * @param   string  $regex
     * @param   string  $replacement
     */
    public function __construct($regex, $replacement) {
        $this->regex       = $regex;
        $this->replacement = $replacement;
    }

    /**
     * @param   string  $selector
     * @return  string
     */
    public function apply($selector) {
        return preg_replace($this->regex, $this->replacement, $selector);
    }
}

interface RuleInterface {
    /**
     * @param   string  $selector
     * @return  string
     */
    public function apply($selector);
}
