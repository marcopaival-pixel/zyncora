<?php

namespace App\Helpers;

class SegmentHelper
{
    public static function getPrimarySegments(): array
    {
        return [
            'Saúde' => 'heroicon-o-heart',
            'Fitness' => 'heroicon-o-bolt',
            'Educação' => 'heroicon-o-academic-cap',
            'Jurídico' => 'heroicon-o-scale',
            'Contabilidade' => 'heroicon-o-calculator',
            'Imobiliário' => 'heroicon-o-home-modern',
            'Automotivo' => 'heroicon-o-truck',
            'Comércio' => 'heroicon-o-building-storefront',
            'E-commerce' => 'heroicon-o-shopping-bag',
            'Alimentação' => 'heroicon-o-cake',
            'Beleza' => 'heroicon-o-sparkles',
            'Hotelaria e Turismo' => 'heroicon-o-map',
            'Serviços' => 'heroicon-o-wrench-screwdriver',
            'Outro' => 'heroicon-o-squares-plus',
        ];
    }

    public static function getSecondarySegments(): array
    {
        return [
            'Saúde' => [
                'Clínica Médica' => 'Clínica Médica',
                'Clínica Popular' => 'Clínica Popular',
                'Clínica Multidisciplinar' => 'Clínica Multidisciplinar',
                'Odontologia' => 'Odontologia',
                'Ortodontia' => 'Ortodontia',
                'Psicologia' => 'Psicologia',
                'Psiquiatria' => 'Psiquiatria',
                'Nutrição' => 'Nutrição',
                'Fisioterapia' => 'Fisioterapia',
                'Fonoaudiologia' => 'Fonoaudiologia',
                'Dermatologia' => 'Dermatologia',
                'Oftalmologia' => 'Oftalmologia',
                'Pediatria' => 'Pediatria',
                'Cardiologia' => 'Cardiologia',
                'Veterinária' => 'Veterinária',
                'Laboratório' => 'Laboratório',
                'Clínica de Estética' => 'Clínica de Estética',
                'Medicina do Trabalho' => 'Medicina do Trabalho',
            ],
            'Fitness' => [
                'Academia' => 'Academia',
                'Crossfit' => 'Crossfit',
                'Pilates' => 'Pilates',
                'Personal Trainer' => 'Personal Trainer',
                'Funcional' => 'Funcional',
                'Artes Marciais' => 'Artes Marciais',
                'Escola de Dança' => 'Escola de Dança',
                'Yoga' => 'Yoga',
            ],
            'Educação' => [
                'Escola Infantil' => 'Escola Infantil',
                'Escola Fundamental' => 'Escola Fundamental',
                'Escola Particular' => 'Escola Particular',
                'Faculdade' => 'Faculdade',
                'Universidade' => 'Universidade',
                'Curso Técnico' => 'Curso Técnico',
                'Curso Profissionalizante' => 'Curso Profissionalizante',
                'Curso de Idiomas' => 'Curso de Idiomas',
                'Curso Online' => 'Curso Online',
                'Reforço Escolar' => 'Reforço Escolar',
            ],
            'Jurídico' => [
                'Escritório de Advocacia' => 'Escritório de Advocacia',
                'Advogado Autônomo' => 'Advogado Autônomo',
                'Consultoria Jurídica' => 'Consultoria Jurídica',
            ],
            'Contabilidade' => [
                'Escritório Contábil' => 'Escritório Contábil',
                'BPO Financeiro' => 'BPO Financeiro',
                'Consultoria Empresarial' => 'Consultoria Empresarial',
            ],
            'Imobiliário' => [
                'Imobiliária' => 'Imobiliária',
                'Corretor de Imóveis' => 'Corretor de Imóveis',
                'Incorporadora' => 'Incorporadora',
                'Construtora' => 'Construtora',
            ],
            'Automotivo' => [
                'Oficina Mecânica' => 'Oficina Mecânica',
                'Auto Elétrica' => 'Auto Elétrica',
                'Centro Automotivo' => 'Centro Automotivo',
                'Funilaria' => 'Funilaria',
                'Loja de Veículos' => 'Loja de Veículos',
                'Lava Jato' => 'Lava Jato',
            ],
            'Comércio' => [
                'Loja de Roupas' => 'Loja de Roupas',
                'Loja de Calçados' => 'Loja de Calçados',
                'Loja de Cosméticos' => 'Loja de Cosméticos',
                'Loja de Informática' => 'Loja de Informática',
                'Loja de Celulares' => 'Loja de Celulares',
                'Loja de Móveis' => 'Loja de Móveis',
                'Material de Construção' => 'Material de Construção',
                'Papelaria' => 'Papelaria',
                'Pet Shop' => 'Pet Shop',
            ],
            'E-commerce' => [
                'Loja Virtual' => 'Loja Virtual',
                'Marketplace' => 'Marketplace',
                'Dropshipping' => 'Dropshipping',
            ],
            'Alimentação' => [
                'Restaurante' => 'Restaurante',
                'Pizzaria' => 'Pizzaria',
                'Hamburgueria' => 'Hamburgueria',
                'Lanchonete' => 'Lanchonete',
                'Cafeteria' => 'Cafeteria',
                'Padaria' => 'Padaria',
                'Confeitaria' => 'Confeitaria',
                'Delivery' => 'Delivery',
            ],
            'Beleza' => [
                'Salão de Beleza' => 'Salão de Beleza',
                'Barbearia' => 'Barbearia',
                'Clínica de Estética (Beleza)' => 'Clínica de Estética (Beleza)',
                'Nail Designer' => 'Nail Designer',
                'Maquiadora' => 'Maquiadora',
            ],
            'Hotelaria e Turismo' => [
                'Hotel' => 'Hotel',
                'Pousada' => 'Pousada',
                'Agência de Turismo' => 'Agência de Turismo',
                'Locadora de Veículos' => 'Locadora de Veículos',
            ],
            'Serviços' => [
                'Segurança Eletrônica' => 'Segurança Eletrônica',
                'Limpeza' => 'Limpeza',
                'Jardinagem' => 'Jardinagem',
                'Dedetização' => 'Dedetização',
                'Mudanças' => 'Mudanças',
                'Transportadora' => 'Transportadora',
                'Consultoria' => 'Consultoria',
                'Agência de Marketing' => 'Agência de Marketing',
                'Software House' => 'Software House',
            ],
            'Outros' => [
                'Outro Segmento' => 'Outro Segmento',
            ],
        ];
    }
}
