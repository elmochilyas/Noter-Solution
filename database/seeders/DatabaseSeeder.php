<?php

namespace Database\Seeders;

use App\Models\AvailabilityRule;
use App\Models\Client;
use App\Models\ConsultationPlan;
use App\Models\Faq;
use App\Models\Service;
use Database\Factories\BookingFactory;
use Database\Factories\ClientFactory;
use Database\Factories\ConsultationPlanFactory;
use Database\Factories\PaymentFactory;
use Database\Factories\UserFactory;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $ownerRole = Role::create(['name' => 'owner', 'guard_name' => 'web']);
        $assistantRole = Role::create(['name' => 'assistant', 'guard_name' => 'web']);

        UserFactory::new()->owner()->create([
            'name' => 'Sana Bouhamidi',
            'email' => 'sana@noter.ma',
        ])->assignRole($ownerRole);

        UserFactory::new()->assistant()->create([
            'name' => 'Assistant Noter',
            'email' => 'assistant@noter.ma',
        ])->assignRole($assistantRole);

        ConsultationPlanFactory::new()->free()->create([
            'slug' => 'orientation-gratuite',
            'name_translations' => [
                'fr' => 'Orientation Gratuite',
                'ar' => 'استشارة تعريفية مجانية',
            ],
            'description_translations' => [
                'fr' => 'Première consultation gratuite de 20 minutes pour découvrir nos services.',
                'ar' => 'استشارة أولى مجانية لمدة 20 دقيقة للتعرف على خدماتنا.',
            ],
            'included_features' => [
                'fr' => ['Consultation de 20 minutes', 'Conseil personnalisé', 'Sans engagement'],
                'ar' => ['استشارة لمدة 20 دقيقة', 'نصيحة مخصصة', 'بدون التزام'],
            ],
            'duration_minutes' => 20,
            'price_centimes' => 0,
            'format' => 'both',
            'is_recommended' => false,
            'display_order' => 1,
        ]);

        ConsultationPlanFactory::new()->create([
            'slug' => 'consultation-express',
            'name_translations' => [
                'fr' => 'Consultation Express',
                'ar' => 'استشارة سريعة',
            ],
            'description_translations' => [
                'fr' => 'Consultation de 30 minutes par visio ou au cabinet pour une question précise.',
                'ar' => 'استشارة لمدة 30 دقيقة عبر الفيديو أو في المكتب لسؤال محدد.',
            ],
            'included_features' => [
                'fr' => ['Consultation de 30 minutes', 'Disponible en visio ou présentiel', 'Réponse à une question spécifique'],
                'ar' => ['استشارة لمدة 30 دقيقة', 'متاحة عبر الفيديو أو حضوريًا', 'الإجابة على سؤال محدد'],
            ],
            'duration_minutes' => 30,
            'price_centimes' => 15000,
            'format' => 'both',
            'is_recommended' => false,
            'display_order' => 2,
        ]);

        ConsultationPlanFactory::new()->recommended()->create([
            'slug' => 'consultation-standard',
            'name_translations' => [
                'fr' => 'Consultation Standard',
                'ar' => 'استشارة قياسية',
            ],
            'description_translations' => [
                'fr' => 'Consultation complète d\'une heure pour traiter vos dossiers en profondeur.',
                'ar' => 'استشارة كاملة لمدة ساعة لمعالجة ملفاتك بعمق.',
            ],
            'included_features' => [
                'fr' => ['Consultation d\'une heure', 'Analyse approfondie', 'Compte rendu écrit', 'Suivi email sous 48h'],
                'ar' => ['استشارة لمدة ساعة', 'تحليل متعمق', 'تقرير مكتوب', 'متابعة بالبريد الإلكتروني خلال 48 ساعة'],
            ],
            'duration_minutes' => 60,
            'price_centimes' => 30000,
            'format' => 'both',
            'is_recommended' => true,
            'display_order' => 3,
        ]);

        ConsultationPlanFactory::new()->create([
            'slug' => 'consultation-premium',
            'name_translations' => [
                'fr' => 'Consultation Premium',
                'ar' => 'استشارة بريميوم',
            ],
            'description_translations' => [
                'fr' => 'Consultation approfondie de 90 minutes avec priorité de suivi.',
                'ar' => 'استشارة معمقة لمدة 90 دقيقة مع أولوية المتابعة.',
            ],
            'included_features' => [
                'fr' => ['Consultation de 90 minutes', 'Analyse exhaustive', 'Compte rendu détaillé', 'Suivi prioritaire 7 jours', 'Accès direct par WhatsApp'],
                'ar' => ['استشارة لمدة 90 دقيقة', 'تحليل شامل', 'تقرير مفصل', 'متابعة ذات أولوية لمدة 7 أيام', 'وصول مباشر عبر الواتساب'],
            ],
            'duration_minutes' => 90,
            'price_centimes' => 50000,
            'format' => 'both',
            'is_recommended' => false,
            'display_order' => 4,
        ]);

        for ($day = 1; $day <= 5; $day++) {
            AvailabilityRule::create([
                'day_of_week' => $day,
                'starts_at' => '09:00',
                'ends_at' => '12:30',
                'format' => 'both',
                'is_active' => true,
            ]);
            AvailabilityRule::create([
                'day_of_week' => $day,
                'starts_at' => '14:00',
                'ends_at' => '18:00',
                'format' => 'both',
                'is_active' => true,
            ]);
        }

        $faqs = [
            ['category' => 'general', 'question_fr' => 'Quels sont vos horaires d\'ouverture ?', 'question_ar' => 'ما هي ساعات العمل؟'],
            ['category' => 'general', 'question_fr' => 'Où se trouve votre cabinet ?', 'question_ar' => 'أين يقع مكتبك؟'],
            ['category' => 'general', 'question_fr' => 'Quels documents dois-je apporter ?', 'question_ar' => 'ما هي المستندات التي يجب إحضارها؟'],
            ['category' => 'booking', 'question_fr' => 'Comment annuler un rendez-vous ?', 'question_ar' => 'كيف يمكن إلغاء موعد؟'],
            ['category' => 'booking', 'question_fr' => 'Puis-je modifier mon rendez-vous ?', 'question_ar' => 'هل يمكنني تعديل موعدي؟'],
            ['category' => 'booking', 'question_fr' => 'Combien de temps dure une consultation ?', 'question_ar' => 'كم تستغرق الاستشارة؟'],
            ['category' => 'booking', 'question_fr' => 'Est-ce que les consultations en visio sont possibles ?', 'question_ar' => 'هل الاستشارات عبر الفيديو ممكنة؟'],
            ['category' => 'payment', 'question_fr' => 'Quels moyens de paiement acceptez-vous ?', 'question_ar' => 'ما هي وسائل الدفع المقبولة؟'],
            ['category' => 'payment', 'question_fr' => 'Puis-je payer en plusieurs fois ?', 'question_ar' => 'هل يمكن الدفع على أقساط؟'],
            ['category' => 'payment', 'question_fr' => 'La consultation gratuite est-elle vraiment sans frais ?', 'question_ar' => 'هل الاستشارة المجانية حقًا بدون رسوم؟'],
            ['category' => 'payment', 'question_fr' => 'Comment obtenir une facture ?', 'question_ar' => 'كيف يمكن الحصول على فاتورة؟'],
            ['category' => 'payment', 'question_fr' => 'Quels sont les tarifs des consultations ?', 'question_ar' => 'ما هي أسعار الاستشارات؟'],
            ['category' => 'services', 'question_fr' => 'Proposez-vous des services de traduction de documents ?', 'question_ar' => 'هل تقدمون خدمات ترجمة المستندات؟'],
            ['category' => 'services', 'question_fr' => 'Faites-vous des actes de mariage ?', 'question_ar' => 'هل تقومون بعقود الزواج؟'],
            ['category' => 'services', 'question_fr' => 'Puis-je faire une procuration chez vous ?', 'question_ar' => 'هل يمكن عمل توكيل لديكم؟'],
            ['category' => 'services', 'question_fr' => 'Proposez-vous des services de création d\'entreprise ?', 'question_ar' => 'هل تقدمون خدمات تأسيس الشركات؟'],
            ['category' => 'services', 'question_fr' => 'Faites-vous des transactions immobilières ?', 'question_ar' => 'هل تقومون بالمعاملات العقارية؟'],
            ['category' => 'general', 'question_fr' => 'Parlez-vous anglais ?', 'question_ar' => 'هل تتحدث الإنجليزية؟'],
            ['category' => 'general', 'question_fr' => 'Puis-je venir sans rendez-vous ?', 'question_ar' => 'هل يمكن القدوم بدون موعد؟'],
            ['category' => 'booking', 'question_fr' => 'Comment prendre rendez-vous en ligne ?', 'question_ar' => 'كيف يمكن حجز موعد عبر الإنترنت؟'],
            ['category' => 'booking', 'question_fr' => 'Que faire si je suis en retard ?', 'question_ar' => 'ماذا أفعل إذا تأخرت؟'],
            ['category' => 'payment', 'question_fr' => 'Acceptez-vous la CNSS ou la mutuelle ?', 'question_ar' => 'هل تقبلون CNSS أو التأمين الصحي؟'],
            ['category' => 'payment', 'question_fr' => 'Y a-t-il des frais d\'annulation ?', 'question_ar' => 'هل توجد رسوم إلغاء؟'],
            ['category' => 'services', 'question_fr' => 'Faites-vous des successions ?', 'question_ar' => 'هل تقومون بقضايا الإرث؟'],
            ['category' => 'services', 'question_fr' => 'Proposez-vous des consultations pour les Marocains résidant à l\'étranger ?', 'question_ar' => 'هل تقدمون استشارات للمغاربة المقيمين بالخارج؟'],
            ['category' => 'general', 'question_fr' => 'Quels sont les délais de réponse par email ?', 'question_ar' => 'ما هي مدة الرد عبر البريد الإلكتروني؟'],
            ['category' => 'general', 'question_fr' => 'Comment puis-je vous contacter en urgence ?', 'question_ar' => 'كيف يمكن الاتصال بك في حالة طارئة؟'],
            ['category' => 'booking', 'question_fr' => 'Puis-je réserver pour quelqu\'un d\'autre ?', 'question_ar' => 'هل يمكن حجز موعد لشخص آخر؟'],
            ['category' => 'services', 'question_fr' => 'Faites-vous des certifications de signatures ?', 'question_ar' => 'هل تقومون بتصديق التوقيعات؟'],
            ['category' => 'payment', 'question_fr' => 'Proposez-vous le paiement par WhatsApp ?', 'question_ar' => 'هل توفرون الدفع عبر الواتساب؟'],
        ];

        foreach ($faqs as $i => $faq) {
            Faq::create([
                'category' => $faq['category'],
                'question_translations' => [
                    'fr' => $faq['question_fr'],
                    'ar' => $faq['question_ar'],
                ],
                'answer_translations' => [
                    'fr' => 'Réponse à la question : '.$faq['question_fr'],
                    'ar' => 'إجابة على السؤال: '.$faq['question_ar'],
                ],
                'is_published' => true,
                'display_order' => $i + 1,
                'view_count' => 0,
            ]);
        }

        $services = [
            [
                'slug' => 'actes-familiaux',
                'title_fr' => 'Actes Familiaux',
                'title_ar' => 'العقود الأسرية',
                'icon' => 'heart',
            ],
            [
                'slug' => 'immobilier',
                'title_fr' => 'Immobilier',
                'title_ar' => 'العقارات',
                'icon' => 'home',
            ],
            [
                'slug' => 'entreprise',
                'title_fr' => 'Entreprise & Commerce',
                'title_ar' => 'الأعمال والتجارة',
                'icon' => 'briefcase',
            ],
            [
                'slug' => 'contentieux',
                'title_fr' => 'Contentieux & Recouvrement',
                'title_ar' => 'المنازعات والتحصيل',
                'icon' => 'scale',
            ],
        ];

        foreach ($services as $i => $svc) {
            Service::create([
                'slug' => $svc['slug'],
                'title_translations' => ['fr' => $svc['title_fr'], 'ar' => $svc['title_ar']],
                'intro_translations' => ['fr' => 'Introduction pour '.$svc['title_fr'], 'ar' => 'مقدمة لـ '.$svc['title_ar']],
                'body_translations' => ['fr' => 'Contenu détaillé pour '.$svc['title_fr'], 'ar' => 'محتوى مفصل لـ '.$svc['title_ar']],
                'transactions_translations' => ['fr' => 'Transactions pour '.$svc['title_fr'], 'ar' => 'المعاملات لـ '.$svc['title_ar']],
                'required_documents_translations' => ['fr' => 'Documents requis pour '.$svc['title_fr'], 'ar' => 'المستندات المطلوبة لـ '.$svc['title_ar']],
                'icon' => $svc['icon'],
                'display_order' => $i + 1,
                'is_active' => true,
            ]);
        }

        ClientFactory::new()->count(5)->create()->each(function (Client $client) {
            $plan = ConsultationPlan::inRandomOrder()->first();
            $booking = BookingFactory::new()->confirmed()->create([
                'client_id' => $client->id,
                'consultation_plan_id' => $plan->id,
            ]);

            PaymentFactory::new()->succeeded()->create([
                'booking_id' => $booking->id,
            ]);
        });
    }
}
