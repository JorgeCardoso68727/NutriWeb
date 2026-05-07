<?php

use yii\db\Migration;

/**
 * Inserts initial recipe tags and event categories.
 */
class m260505_000002_insert_initial_data extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Insert recipe tags
        $this->insert('recipe_tag', [
            'name' => 'Vegetariana',
            'description' => 'Receitas sem carne'
        ]);

        $this->insert('recipe_tag', [
            'name' => 'Vegan',
            'description' => 'Receitas sem produtos de origem animal'
        ]);

        $this->insert('recipe_tag', [
            'name' => 'Sem Glúten',
            'description' => 'Receitas seguras para celíacos'
        ]);

        $this->insert('recipe_tag', [
            'name' => 'Baixo em Hidratos de Carbono',
            'description' => 'Receitas com reduzidos hidratos de carbono'
        ]);

        $this->insert('recipe_tag', [
            'name' => 'Orgânico',
            'description' => 'Receitas com ingredientes biológicos'
        ]);

        $this->insert('recipe_tag', [
            'name' => 'Sem Açúcar',
            'description' => 'Receitas sem açúcar adicionado'
        ]);

        $this->insert('recipe_tag', [
            'name' => 'Alto em Proteína',
            'description' => 'Receitas ricas em proteína'
        ]);

        $this->insert('recipe_tag', [
            'name' => 'Paleo',
            'description' => 'Receitas baseadas na dieta paleolítica'
        ]);

        // Insert event categories
        $this->insert('event_category', [
            'name' => 'Workshop de Culinária Saudável',
            'description' => 'Workshops sobre técnicas de culinária saudável'
        ]);

        $this->insert('event_category', [
            'name' => 'Palestra sobre Nutrição',
            'description' => 'Palestras educativas sobre nutrição e bem-estar'
        ]);

        $this->insert('event_category', [
            'name' => 'Sessão de Sensibilização',
            'description' => 'Sessões de sensibilização sobre temas de saúde'
        ]);

        $this->insert('event_category', [
            'name' => 'Feira de Produtos Biológicos',
            'description' => 'Feiras dedicadas a produtos biológicos e locais'
        ]);

        $this->insert('event_category', [
            'name' => 'Demonstração Culinária',
            'description' => 'Demonstrações práticas de culinária'
        ]);

        $this->insert('event_category', [
            'name' => 'Encontro de Nutricionistas',
            'description' => 'Encontros para profissionais de nutrição'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Delete recipe tags
        $this->delete('recipe_tag', ['name' => 'Vegetariana']);
        $this->delete('recipe_tag', ['name' => 'Vegan']);
        $this->delete('recipe_tag', ['name' => 'Sem Glúten']);
        $this->delete('recipe_tag', ['name' => 'Baixo em Hidratos de Carbono']);
        $this->delete('recipe_tag', ['name' => 'Orgânico']);
        $this->delete('recipe_tag', ['name' => 'Sem Açúcar']);
        $this->delete('recipe_tag', ['name' => 'Alto em Proteína']);
        $this->delete('recipe_tag', ['name' => 'Paleo']);

        // Delete event categories
        $this->delete('event_category', ['name' => 'Workshop de Culinária Saudável']);
        $this->delete('event_category', ['name' => 'Palestra sobre Nutrição']);
        $this->delete('event_category', ['name' => 'Sessão de Sensibilização']);
        $this->delete('event_category', ['name' => 'Feira de Produtos Biológicos']);
        $this->delete('event_category', ['name' => 'Demonstração Culinária']);
        $this->delete('event_category', ['name' => 'Encontro de Nutricionistas']);
    }
}
