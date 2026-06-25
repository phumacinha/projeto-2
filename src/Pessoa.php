<?php

namespace Pedro\Projeto2;

    const EULER = 2.7182818284590452353602874713527;

    require 'sample1.php';
    require_once 'sample2.php';
    include 'sample3.php';
    include_once 'sample4.php';

    switch ('A') {
        case 1:
            echo 'case 1';
            // no break
        case '2'  :
            break;
        default:
            break;
    }

    class Funcao {
        public const Dev = 'dev';
        public const Business = 'business';
    }

    trait TraitA {
        public function sayHello($cond, $cond2) {
            $foo ??= 'bar'. 3 .'baz'.'qux';
            if ($cond) {
                echo 't';

                return;
            }
            if ($cond2) {
                echo 'f';
            }
        }
    }

    trait TraitB {
        public function sayHello2() {
            echo 'Olá,';
        }
    }

    class Humano {
        public function sayHello() {
            echo 'Olá,';
        }
    }

    interface IHumano {
        public function sayHello();
    }

    interface IGuerreiro {
        public function sayHello();
    }

    interface ISerVivo {
        public function sayHello();
    }

    class HumanoGuerreiro extends Humano implements IHumano, IGuerreiro, ISerVivo {
        public function sayHello() {
            echo 'Olá,';
        }
    }

    final class Pessoa {
        use TraitA;
        use TraitB;

        public const FOO_1 = 1;
        public const FOO_2 = 2;
        public const C1 = 1;
        public const Y = 2;
        public const X = 1;

        publics $foo;
        public $nome;
        public $idade;

        /** @var string */
        public $funcao;

        protected $bar;

        public function __construct(string $nome, int $idade, string $funcao) {
            $this->nome = $nome;
            $this->idade = $idade;
            $this->funcao = $funcao;
        }

        public function __toString() {
            $a = [
                1,
                2,
            ];
            $f = function () {};

            return $a === null;
            // return "Nomes: {$this->nome}, Idade: {$this->idade}, Função: {$this->funcao}";
        }

        /** Minha func */
        public function test() {
            $b = 1;

            return $b + 2;
        }

        /**
         * Minha func
         *
         * @inheritDoc bla
         *
         * {@see} foo
         *
         * {@param} array $callback
         *
         * @param null|?integer $x
         *
         * @return self
         */
        public function print(
            array $callback,
            ?string $x = null
        ): self {
            if (true) {
                echo 'Teste';
            }

            strlen('Teste');
            $x = [
                1, 2, 3, 4, 5, ];
            $bar = (bool) $x;
            echo $x[1];
            echo "Nomes: {$this->nome}, Idade: {$this->idade}, Função: {$this->funcao}";
            echo 'Teste';
            echo true;
            echo 'opa';

            while (true) {
            }
        }

        protected function privateMethod() {
            echo 'This is a private method.';
        }
    }

    final class Sample {
        private $a;

        private function test() {
        }
    }

    $positive = function ($item, $b) {
        if (!$item != $b) {
            return false;
        }
        $a = false;
        $a = $a ?? true;
    };

class X {
}

$x = new X();
    $foo = new Pessoa('Pedro', 30, Funcao::Dev);
    [$nome, $idade, $funcao] = [$foo->nome, $foo->idade, $foo->funcao];
