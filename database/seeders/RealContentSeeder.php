<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\Service;
use Illuminate\Database\Seeder;

class RealContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->updateServices();
        $this->updateFaqs();
    }

    private function updateServices(): void
    {
        $data = [
            'actes-familiaux' => [
                'intro' => [
                    'fr' => 'Maître Sana Bouhamidi, Notaire Adoul à Agadir, vous accompagne dans tous vos actes liés au droit de la famille : mariage, divorce, filiation, adoption, succession et testament. Bénéficiez d\'un accompagnement personnalisé et conforme à la loi marocaine.',
                    'ar' => 'تواكبكم الأستاذة سناء بوحميدي، عدلة بمدينة أكادير، في جميع عقودكم المتعلقة بقانون الأسرة: الزواج، الطلاق، إثبات النسب، التبني، الإرث والوصية. استفيدوا من مواكبة شخصية ومطابقة للقانون المغربي.',
                ],
                'body' => [
                    'fr' => "Le droit de la famille constitue le cœur de notre activité notariale. Nous intervenons pour la rédaction et l'authentification des actes de mariage, en veillant au respect des formalités légales et à la protection des intérêts des deux époux.\n\nNotre cabinet vous assiste également dans les procédures de divorce, qu'il s'agisse de divorce amiable ou contentieux, ainsi que dans les reconnaissances d'enfant et les adoptions.\n\nEn matière successorale, nous vous guidons dans le calcul des parts, le partage des biens et la rédaction des testaments, afin d'assurer une transmission sereine de votre patrimoine.",
                    'ar' => "يشكل قانون الأسرة جوهر نشاطنا العدلي. نتدخل لتحرير وتوثيق عقود الزواج، مع الحرص على احترام الإجراءات القانونية وحماية مصالح الزوجين.\n\nيساعدكم مكتبنا أيضًا في إجراءات الطلاق، سواء كان الطلاق بالتراضي أو الطلاق القضائي، وكذلك في إثبات النسب والتبني.\n\nفي مادة الإرث، نرشدكم في حساب الأنصبة وتقسيم التركة وتحرير الوصايا، لضمان نقل هادئ لممتلكاتكم.",
                ],
            ],
            'immobilier' => [
                'intro' => [
                    'fr' => 'Confiez vos transactions immobilières à un professionnel du droit. Achat, vente, location, hypothèque ou copropriété : Maître Bouhamidi sécurise vos projets immobiliers à Agadir et dans toute la région.',
                    'ar' => 'أسندوا معاملاتكم العقارية لمحترفة في القانون. الشراء، البيع، الكراء، الرهن العقاري أو الملكية المشتركة: تؤمن الأستاذة بوحميدي مشاريعكم العقارية بأكادير وفي جميع أنحاء المنطقة.',
                ],
                'body' => [
                    'fr' => "Le secteur immobilier requiert une expertise juridique pointue pour sécuriser chaque transaction. Nous rédigeons et authentifions les actes de vente, les promesses de vente et les contrats de location.\n\nNotre cabinet vous accompagne dans les opérations d'hypothèque et de crédit immobilier, en veillant à la régularité des inscriptions et à la protection de vos droits.\n\nNous traitons également les dossiers de copropriété, de viager et de division foncière, avec une connaissance approfondie du droit immobilier marocain.",
                    'ar' => "يتطلب القطاع العقاري خبرة قانونية دقيقة لتأمين كل معاملة. نحرر ونوثق عقود البيع، ووعود البيع وعقود الكراء.\n\nيرافقكم مكتبنا في عمليات الرهن العقاري والقرض العقاري، مع الحرص على سلامة القيود وحماية حقوقكم.\n\nنتعامل أيضًا مع ملفات الملكية المشتركة والبيع مدى الحياة والتجزئة العقارية، بمعرفة عميقة بالقانون العقاري المغربي.",
                ],
            ],
            'entreprise' => [
                'intro' => [
                    'fr' => 'Donnez vie à vos projets entrepreneuriaux en toute sérénité. Création de société, cession, fusion, dissolution ou dépôt de marque : Maître Bouhamidi vous conseille et vous représente.',
                    'ar' => 'أحيوا مشاريعكم المقاولاتية بكل طمأنينة. تأسيس الشركات، التنازل، الاندماج، حل الشركة أو إيداع العلامات التجارية: تنصحكم وتمثلكم الأستاذة بوحميدي.',
                ],
                'body' => [
                    'fr' => "Nous accompagnons les entrepreneurs et chefs d'entreprise dans toutes les étapes de la vie de leur société, de sa création à sa dissolution.\n\nNotre cabinet rédige les statuts, gère les formalités d'immatriculation et vous conseille sur la forme juridique la plus adaptée à votre activité.\n\nNous intervenons également dans les opérations de cession de parts sociales, de fusion-acquisition et de dépôt de marque auprès de l'OMPIC, avec une approche sur-mesure pour chaque client.",
                    'ar' => "نواكب المقاولين ومسيري الشركات في جميع مراحل حياة شركاتهم، من التأسيس إلى الحل.\n\nيحرر مكتبنا النظام الأساسي، ويدير إجراءات التسجيل وينصحكم بشأن الشكل القانوني الأنسب لنشاطكم.\n\nنتدخل أيضًا في عمليات التنازل عن الحصص الاجتماعية والاندماج والاستحواذ وإيداع العلامات التجارية لدى OMPIC، بنهج مخصص لكل زبون.",
                ],
            ],
            'contentieux' => [
                'intro' => [
                    'fr' => 'Face à un litige ou des difficultés de recouvrement, Maître Bouhamidi défend vos droits avec rigueur et efficacité. Procédures judiciaires, recouvrement de créances et saisies : une assistance juridique complète.',
                    'ar' => 'في مواجهة نزاع أو صعوبات في التحصيل، تدافع الأستاذة بوحميدي عن حقوقكم بصرامة وفعالية. المساطر القضائية، تحصيل الديون والحجز: مساعدة قانونية شاملة.',
                ],
                'body' => [
                    'fr' => "Notre cabinet vous assiste dans toutes les procédures contentieuses, en privilégiant les solutions amiables avant d'envisager la voie judiciaire.\n\nEn matière de recouvrement de créances, nous mettons en œuvre des stratégies efficaces : mise en demeure, négociation, puis procédures de saisie si nécessaire.\n\nNous vous représentons devant les juridictions compétentes et assurons un suivi rigoureux de chaque dossier, avec une communication transparente sur l'avancement de vos affaires.",
                    'ar' => "يساعدكم مكتبنا في جميع المساطر القضائية، مع تفضيل الحلول الودية قبل اللجوء إلى القضاء.\n\nفي مادة تحصيل الديون، نعتمد استراتيجيات فعالة: الإنذار، التفاوض، ثم إجراءات الحجز عند الضرورة.\n\nنمثلكم أمام المحاكم المختصة ونتابع بدقة كل ملف، مع تواصل شفاف حول سير قضاياكم.",
                ],
            ],
        ];

        foreach ($data as $slug => $content) {
            $service = Service::where('slug', $slug)->first();
            if ($service) {
                $service->update([
                    'intro_translations' => $content['intro'],
                    'body_translations' => $content['body'],
                ]);
                $this->command->info("Service '{$slug}' updated.");
            } else {
                $this->command->warn("Service '{$slug}' not found.");
            }
        }
    }

    private function updateFaqs(): void
    {
        $answers = [
            [
                'q_fr' => "Quels sont vos horaires d'ouverture ?",
                'fr' => "Notre cabinet est ouvert du lundi au vendredi, de 9h00 à 12h30 et de 14h00 à 18h00. Nous sommes également joignables par téléphone et email pendant ces horaires. Pour un rendez-vous en dehors de ces créneaux, n'hésitez pas à nous contacter afin d'étudier les possibilités.",
                'ar' => 'مكتبنا مفتوح من الاثنين إلى الجمعة، من الساعة 9:00 إلى 12:30 ومن 14:00 إلى 18:00. يمكن الاتصال بنا هاتفياً وعبر البريد الإلكتروني خلال هذه الأوقات. للمواعيد خارج هذه الفترات، لا تترددوا في الاتصال بنا لدراسة الإمكانيات.',
            ],
            [
                'q_fr' => 'Où se trouve votre cabinet ?',
                'fr' => "Notre cabinet est situé à Agadir. L'adresse exacte vous sera communiquée lors de la prise de rendez-vous. Nous pouvons également organiser des consultations en visioconférence pour votre confort.",
                'ar' => 'يقع مكتبنا في مدينة أكادير. سيتم إبلاغكم بالعنوان الدقيق عند حجز الموعد. يمكننا أيضًا تنظيم استشارات عبر الفيديو لراحتكم.',
            ],
            [
                'q_fr' => 'Quels documents dois-je apporter ?',
                'fr' => "Les documents nécessaires varient selon le type d'acte. De manière générale, munissez-vous de votre pièce d'identité nationale et de tous les documents en lien avec votre dossier (actes, contrats, jugements, etc.). Lors de la prise de rendez-vous, notre équipe vous indiquera précisément les pièces à fournir.",
                'ar' => 'تختلف الوثائق المطلوبة حسب نوع العقد. بشكل عام، أحضروا بطاقة تعريفكم الوطنية وجميع الوثائق المتعلقة بملفكم (عقود، عقود، أحكام، إلخ). عند حجز الموعد، سيشير فريقنا بدقة إلى الوثائق المطلوبة.',
            ],
            [
                'q_fr' => 'Comment annuler un rendez-vous ?',
                'fr' => "Vous pouvez annuler votre rendez-vous simplement depuis le lien de confirmation reçu par email ou WhatsApp, ou en nous contactant directement par téléphone. Nous vous remercions de nous prévenir au moins 24 heures à l'avance afin de libérer le créneau pour d'autres clients.",
                'ar' => 'يمكنكم إلغاء موعدكم ببساطة عبر رابط التأكيد الذي تلقيتموه عبر البريد الإلكتروني أو الواتساب، أو بالاتصال بنا مباشرة. نشكركم على إعلامنا قبل 24 ساعة على الأقل لإتاحة الموعد لزبائن آخرين.',
            ],
            [
                'q_fr' => 'Puis-je modifier mon rendez-vous ?',
                'fr' => "Oui, il est possible de modifier votre rendez-vous. Contactez-nous par téléphone ou par email au moins 24 heures à l'avance, et nous vous proposerons un nouveau créneau adapté à votre emploi du temps.",
                'ar' => 'نعم، يمكن تعديل موعدكم. اتصلوا بنا هاتفياً أو عبر البريد الإلكتروني قبل 24 ساعة على الأقل، وسنقترح عليكم موعدًا جديدًا يناسب جدولكم.',
            ],
            [
                'q_fr' => 'Combien de temps dure une consultation ?',
                'fr' => "La durée de la consultation dépend de la formule choisie : 20 minutes pour la consultation d'orientation gratuite, 30 minutes pour la consultation express, 60 minutes pour la consultation standard et 90 minutes pour la consultation premium. Le temps nécessaire peut varier selon la complexité de votre dossier.",
                'ar' => 'تختلف مدة الاستشارة حسب الصيغة المختارة: 20 دقيقة للاستشارة التعريفية المجانية، 30 دقيقة للاستشارة السريعة، 60 دقيقة للاستشارة القياسية و90 دقيقة للاستشارة بريميوم. قد تختلف المدة حسب تعقيد ملفكم.',
            ],
            [
                'q_fr' => 'Est-ce que les consultations en visio sont possibles ?',
                'fr' => "Absolument ! Nous proposons des consultations en visioconférence pour tous les types de rendez-vous. Que vous soyez au Maroc ou à l'étranger, vous pouvez bénéficier de nos services à distance. Le lien de visio vous sera envoyé après confirmation de votre rendez-vous.",
                'ar' => 'بالتأكيد! نقدم استشارات عبر الفيديو لجميع أنواع المواعيد. سواء كنتم في المغرب أو بالخارج، يمكنكم الاستفادة من خدماتنا عن بعد. سيتم إرسال رابط الفيديو بعد تأكيد موعدكم.',
            ],
            [
                'q_fr' => 'Quels moyens de paiement acceptez-vous ?',
                'fr' => "Nous acceptons les paiements en espèces, par virement bancaire et par les principales cartes bancaires. Le paiement par WhatsApp (CMI) est également disponible. Le règlement s'effectue avant ou le jour de la consultation, selon la formule choisie.",
                'ar' => 'نقبل الدفع نقدًا وعبر التحويل البنكي وبطاقات الدفع الرئيسية. الدفع عبر الواتساب (CMI) متاح أيضًا. يتم الدفع قبل أو يوم الاستشارة، حسب الصيغة المختارة.',
            ],
            [
                'q_fr' => 'Puis-je payer en plusieurs fois ?',
                'fr' => "Pour le moment, le paiement s'effectue en une seule fois avant ou le jour de la consultation. Nous travaillons à mettre en place des facilités de paiement pour certains de nos services. N'hésitez pas à nous contacter pour discuter de votre situation.",
                'ar' => 'حاليًا، يتم الدفع دفعة واحدة قبل أو يوم الاستشارة. نعمل على توفير تسهيلات في الدفع لبعض خدماتنا. لا تترددوا في الاتصال بنا لمناقشة وضعكم.',
            ],
            [
                'q_fr' => 'La consultation gratuite est-elle vraiment sans frais ?',
                'fr' => "Oui, la consultation d'orientation gratuite de 20 minutes est totalement sans frais et sans engagement. C'est l'occasion de découvrir nos services, de poser vos questions préliminaires et de voir comment nous pouvons vous accompagner dans votre projet.",
                'ar' => 'نعم، الاستشارة التعريفية المجانية لمدة 20 دقيقة هي مجانية تمامًا وبدون أي التزام. إنها فرصة للتعرف على خدماتنا وطرح أسئلتكم الأولية ومعرفة كيف يمكننا مساعدتكم في مشروعكم.',
            ],
            [
                'q_fr' => 'Comment obtenir une facture ?',
                'fr' => "Une facture vous est automatiquement envoyée par email après chaque paiement. Si vous ne l'avez pas reçue, vérifiez vos spams ou contactez-nous et nous vous la ferons parvenir sans délai.",
                'ar' => 'يتم إرسال فاتورة إليكم تلقائيًا عبر البريد الإلكتروني بعد كل عملية دفع. إذا لم تستلموها، تحققوا من البريد غير المرغوب فيه أو اتصلوا بنا وسنرسلها لكم دون تأخير.',
            ],
            [
                'q_fr' => 'Quels sont les tarifs des consultations ?',
                'fr' => "Nos tarifs varient selon le type de consultation : la consultation d'orientation gratuite est sans frais, la consultation express est à 150 DH, la consultation standard à 300 DH et la consultation premium à 500 DH. Ces tarifs sont dégressifs et adaptés à vos besoins. Consultez notre page de réservation pour plus de détails.",
                'ar' => 'تختلف أسعارنا حسب نوع الاستشارة: الاستشارة التعريفية مجانية، الاستشارة السريعة 150 درهمًا، الاستشارة القياسية 300 درهم والاستشارة بريميوم 500 درهم. هذه الأسعار تنازلية ومناسبة لاحتياجاتكم. راجعوا صفحة الحجز لمزيد من التفاصيل.',
            ],
            [
                'q_fr' => 'Proposez-vous des services de traduction de documents ?',
                'fr' => 'Oui, nous proposons des services de traduction de documents juridiques et administratifs (français-arabe). Notre cabinet peut traduire et certifier vos actes de mariage, jugements, contrats et autres documents officiels. Contactez-nous pour un devis personnalisé.',
                'ar' => 'نعم، نقدم خدمات ترجمة الوثائق القانونية والإدارية (الفرنسية-العربية). يمكن لمكتبنا ترجمة وتصديق عقود الزواج والأحكام والعقود والوثائق الرسمية الأخرى. اتصلوا بنا للحصول على عرض أسعار مخصص.',
            ],
            [
                'q_fr' => 'Faites-vous des actes de mariage ?',
                'fr' => "Oui, la rédaction et l'authentification des actes de mariage font partie de nos services principaux. Nous vous accompagnons dans toutes les étapes, de la constitution du dossier à la signature de l'acte, en conformité avec le Code de la Famille marocain.",
                'ar' => 'نعم، يعد تحرير وتوثيق عقود الزواج من خدماتنا الأساسية. نواكبكم في جميع المراحل، من إعداد الملف إلى توقيع العقد، وفقًا لمدونة الأسرة المغربية.',
            ],
            [
                'q_fr' => 'Puis-je faire une procuration chez vous ?',
                'fr' => 'Oui, nous rédigeons et authentifions les procurations (wakala) pour tous types de mandats : administratifs, judiciaires, immobiliers ou bancaires. La procuration peut être établie en français ou en arabe, selon votre besoin.',
                'ar' => 'نعم، نحرر ونوثق الوكالات لجميع أنواعها: الإدارية والقضائية والعقارية والبنكية. يمكن تحرير الوكالة بالفرنسية أو العربية حسب حاجتكم.',
            ],
            [
                'q_fr' => "Proposez-vous des services de création d'entreprise ?",
                'fr' => "Oui, nous accompagnons les entrepreneurs dans la création de leur société : rédaction des statuts, immatriculation au registre de commerce, publication légale et obtention des numéros d'identification. Nous vous conseillons sur la forme juridique la mieux adaptée à votre activité.",
                'ar' => 'نعم، نواكب المقاولين في تأسيس شركاتهم: تحرير النظام الأساسي، التسجيل في السجل التجاري، النشر القانوني والحصول على أرقام التعريف. ننصحكم بشأن الشكل القانوني الأنسب لنشاطكم.',
            ],
            [
                'q_fr' => 'Faites-vous des transactions immobilières ?',
                'fr' => 'Oui, notre cabinet intervient dans toutes les transactions immobilières : achat, vente, location, hypothèque et copropriété. Nous sécurisons chaque opération par une rédaction rigoureuse des actes et une vérification complète de la situation juridique du bien.',
                'ar' => 'نعم، يتدخل مكتبنا في جميع المعاملات العقارية: الشراء، البيع، الكراء، الرهن العقاري والملكية المشتركة. نؤمن كل عملية بتحرير دقيق للعقود والتحقق الكامل من الوضع القانوني للعقار.',
            ],
            [
                'q_fr' => 'Parlez-vous anglais ?',
                'fr' => "Oui, Maître Bouhamidi parle anglais et peut vous recevoir dans cette langue. Nous pouvons également communiquer en français et en arabe (dialectal et classique). N'hésitez pas à préciser votre langue de préférence lors de la prise de rendez-vous.",
                'ar' => 'نعم، تتحدث الأستاذة بوحميدي الإنجليزية ويمكنها استقبالكم بهذه اللغة. يمكننا أيضًا التواصل بالفرنسية والعربية (العامية والفصحى). لا تترددوا في تحديد لغتكم المفضلة عند حجز الموعد.',
            ],
            [
                'q_fr' => 'Puis-je venir sans rendez-vous ?',
                'fr' => "Nous recommandons vivement de prendre rendez-vous afin de vous garantir un créneau dédié et d'éviter l'attente. Cependant, si vous passez au cabinet sans rendez-vous, nous ferons de notre mieux pour vous recevoir selon les disponibilités du moment.",
                'ar' => 'نوصي بشدة بحجز موعد لضمان تخصيص وقت لكم وتجنب الانتظار. ومع ذلك، إذا حضرتم إلى المكتب بدون موعد، سنبذل قصارى جهدنا لاستقبالكم حسب الإمكانيات المتاحة.',
            ],
            [
                'q_fr' => 'Comment prendre rendez-vous en ligne ?',
                'fr' => "C'est très simple ! Rendez-vous sur notre site, cliquez sur « Prendre rendez-vous », choisissez le type de consultation, sélectionnez le créneau qui vous convient et confirmez. Vous recevrez une confirmation par email et/ou WhatsApp avec toutes les informations nécessaires.",
                'ar' => 'الأمر بسيط جدًا! زوروا موقعنا، انقروا على «حجز موعد»، اختاروا نوع الاستشارة واختاروا الموعد الذي يناسبكم وقوموا بتأكيده. ستتلقون تأكيدًا عبر البريد الإلكتروني و/أو الواتساب بجميع المعلومات الضرورية.',
            ],
            [
                'q_fr' => 'Que faire si je suis en retard ?',
                'fr' => 'Si vous avez un retard de moins de 15 minutes, votre consultation pourra avoir lieu normalement dans le temps restant. Au-delà, nous pourrions être amenés à reporter votre rendez-vous. Merci de nous prévenir par téléphone si vous prévoyez un retard.',
                'ar' => 'إذا كان تأخيركم أقل من 15 دقيقة، فستتم استشارتكم بشكل عادي في الوقت المتبقي. بعد ذلك، قد نضطر إلى تأجيل موعدكم. نشكركم على إعلامنا هاتفياً إذا كنتم تتوقعون التأخير.',
            ],
            [
                'q_fr' => 'Acceptez-vous la CNSS ou la mutuelle ?',
                'fr' => 'Actuellement, nos consultations ne sont pas prises en charge par la CNSS ou les mutuelles. Nous restons à votre disposition pour vous fournir une facture détaillée que vous pourrez transmettre à votre organisme complémentaire pour étude de remboursement.',
                'ar' => 'حاليًا، لا يتم تغطية استشاراتنا من طرف CNSS أو شركات التأمين الصحي. نبقى رهن إشارتكم لتزويدكم بفاتورة مفصلة يمكنكم إرسالها لمؤسستكم التكميلية لدراسة إمكانية الاسترداد.',
            ],
            [
                'q_fr' => "Y a-t-il des frais d'annulation ?",
                'fr' => "Non, l'annulation d'un rendez-vous est gratuite si elle est effectuée au moins 24 heures à l'avance. Pour une annulation tardive ou une absence non justifiée, des frais correspondant à 50 % du montant de la consultation pourront être retenus.",
                'ar' => 'لا، إلغاء الموعد مجاني إذا تم قبل 24 ساعة على الأقل. للإلغاء المتأخر أو عدم الحضور بدون عذر، قد يتم احتساب رسوم قدرها 50٪ من قيمة الاستشارة.',
            ],
            [
                'q_fr' => 'Faites-vous des successions ?',
                'fr' => 'Oui, nous traitons les dossiers de succession complets : inventaire des biens, calcul des parts selon les règles successorales marocaines, partage et attribution. Nous vous accompagnons également dans la rédaction de testaments pour organiser votre patrimoine.',
                'ar' => 'نعم، نتعامل مع ملفات الإرث كاملة: حصر التركة، حساب الأنصبة وفق قواعد الإرث المغربية، التقسيم والإسناد. نواكبكم أيضًا في تحرير الوصايا لتنظيم ممتلكاتكم.',
            ],
            [
                'q_fr' => "Proposez-vous des consultations pour les Marocains résidant à l'étranger ?",
                'fr' => "Oui, nous proposons des consultations en visioconférence spécialement adaptées aux Marocains résidant à l'étranger (MRE). Que ce soit pour des questions familiales, immobilières ou successorales, nous vous accompagnons à distance avec les mêmes garanties professionnelles.",
                'ar' => 'نعم، نقدم استشارات عبر الفيديو مكيفة خصيصًا للمغاربة المقيمين بالخارج. سواء تعلق الأمر بمسائل أسرية أو عقارية أو إرثية، نواكبكم عن بعد بنفس الضمانات المهنية.',
            ],
            [
                'q_fr' => 'Quels sont les délais de réponse par email ?',
                'fr' => 'Nous nous efforçons de répondre à tous les emails sous 24 à 48 heures ouvrées. Pour les demandes urgentes, nous vous recommandons de nous contacter par téléphone directement. Les emails envoyés le week-end sont traités à partir du lundi suivant.',
                'ar' => 'نسعى جاهدين للرد على جميع رسائل البريد الإلكتروني في غضون 24 إلى 48 ساعة عمل. للطلبات العاجلة، نوصي بالاتصال بنا هاتفيًا مباشرة. يتم معالجة الرسائل المرسلة في عطلة نهاية الأسبوع ابتداءً من يوم الاثنين.',
            ],
            [
                'q_fr' => 'Comment puis-je vous contacter en urgence ?',
                'fr' => "En cas d'urgence, contactez-nous directement par téléphone au numéro indiqué sur notre site. Pour les clients ayant souscrit à la consultation premium, un accès direct par WhatsApp est également disponible. Nous ferons de notre mieux pour vous répondre dans les plus brefs délais.",
                'ar' => 'في حالة الطوارئ، اتصلوا بنا مباشرة عبر الهاتف على الرقم المذكور في موقعنا. للزبائن المشتركين في الاستشارة بريميوم، يتوفر وصول مباشر عبر الواتساب أيضًا. سنبذل قصارى جهدنا للرد عليكم في أقرب وقت.',
            ],
            [
                'q_fr' => "Puis-je réserver pour quelqu'un d'autre ?",
                'fr' => 'Oui, vous pouvez réserver une consultation pour un proche. Lors de la réservation, veuillez indiquer les coordonnées de la personne concernée dans les remarques. La personne devra être présente (en personne ou en visio) le jour du rendez-vous.',
                'ar' => 'نعم، يمكنكم حجز استشارة لقريب لكم. عند الحجز، يرجى الإشارة إلى معلومات الاتصال بالشخص المعني في الملاحظات. يجب أن يكون الشخص حاضرًا (شخصيًا أو عبر الفيديو) يوم الموعد.',
            ],
            [
                'q_fr' => 'Faites-vous des certifications de signatures ?',
                'fr' => "Oui, nous certifions les signatures sur tous types de documents : contrats, attestations, procurations et autres actes sous seing privé. La personne doit se présenter en personne au cabinet avec sa pièce d'identité et le document à certifier.",
                'ar' => 'نعم، نصادق على التوقيعات على جميع أنواع الوثائق: العقود والشهادات والوكالات وغيرها من الأوراق العرفية. يجب على الشخص الحضور شخصيًا إلى المكتب مع بطاقة تعريفه والوثيقة المراد تصديقها.',
            ],
            [
                'q_fr' => 'Proposez-vous le paiement par WhatsApp ?',
                'fr' => 'Oui, nous proposons le paiement par WhatsApp via CMI (Centre Monétique Interbancaire). Vous recevrez un lien de paiement sécurisé par WhatsApp, vous permettant de régler votre consultation en quelques clics depuis votre téléphone.',
                'ar' => 'نعم، نقدم الدفع عبر الواتساب بواسطة CMI (المركز البنكي المشترك). ستتلقون رابط دفع آمن عبر الواتساب، يمكنكم من تسديد استشارتكم بنقرات قليلة من هاتفكم.',
            ],
        ];

        foreach ($answers as $item) {
            $faq = Faq::where('question_translations->fr', $item['q_fr'])->first();
            if ($faq) {
                $current = $faq->answer_translations;
                $current['fr'] = $item['fr'];
                $current['ar'] = $item['ar'];
                $faq->update(['answer_translations' => $current]);
                $this->command->info("FAQ answer updated: {$item['q_fr']}");
            } else {
                $this->command->warn("FAQ not found: {$item['q_fr']}");
            }
        }
    }
}
