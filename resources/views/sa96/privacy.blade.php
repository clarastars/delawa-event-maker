@php
    $copy = [
        'en' => [
            'title' => 'Privacy Notice — Saudi National Day 96',
            'controller' => 'Data controller',
            'purposes' => 'Purposes',
            'purposes_body' => 'We collect your name, mobile number, and year of birth to register you for Delawa’s Saudi National Day 96 celebration, complete verification, and — if you agree — send ads or offers. You can opt out and delete your data at any time.',
            'basis' => 'Legal basis',
            'basis_body' => 'Consent under the Saudi Personal Data Protection Law (PDPL). You give this consent with one tick on the form. You can withdraw any time.',
            'categories' => 'Personal data',
            'categories_body' => 'Name, mobile number, year of birth, language preference, consent choices, consent timestamp, notice version, IP address, and browser type. We do not collect national ID, health, or other sensitive data.',
            'recipients' => 'Recipients',
            'recipients_body' => 'Delawa staff who operate this campaign, and hosting / security providers that store or protect the application. We do not sell your data.',
            'transfer' => 'Cross-border transfer',
            'transfer_body' => 'The application may run on infrastructure outside the Kingdom. That transfer happens only with your explicit consent and only to operate this campaign.',
            'retention' => 'Retention',
            'retention_body' => 'Active registrations are kept for :days days from collection, then deleted. If you withdraw consent, personal identifiers are deleted immediately and the remaining audit record is removed within 30 days.',
            'rights' => 'Your rights',
            'rights_body' => 'You may request access, correction, or destruction, and you may withdraw consent at any time. Withdrawal is as easy as registration: use the withdraw form. You may also email :email. You may lodge a complaint with SDAIA.',
            'automated' => 'Automated decisions',
            'automated_body' => 'We do not make solely automated decisions that produce legal or similarly significant effects about you. Year of birth is used only to confirm that you are 18 or older.',
            'children' => 'Children',
            'children_body' => 'This form is for adults. We do not knowingly register anyone under 18.',
            'contact' => 'Contact',
            'version' => 'Notice version',
            'back' => 'Back to registration',
            'withdraw' => 'Withdraw consent',
        ],
        'ar' => [
            'title' => 'إشعار الخصوصية — اليوم الوطني السعودي 96',
            'controller' => 'جهة التحكم',
            'purposes' => 'الأغراض',
            'purposes_body' => 'نجمع اسمك ورقم جوالك وسنة ميلادك لتسجيلك في احتفال ديلاوة باليوم الوطني السعودي 96، ولإكمال عملية التحقق، وقد نرسل لك إعلانات أو عروض إذا وافقت. يمكنك الإلغاء وحذف بياناتك في أي وقت.',
            'basis' => 'الأساس النظامي',
            'basis_body' => 'الموافقة بموجب نظام حماية البيانات الشخصية. تقدّم هذه الموافقة بمربع واحد في النموذج، ويمكنك سحبها في أي وقت.',
            'categories' => 'البيانات الشخصية',
            'categories_body' => 'الاسم، رقم الجوال، سنة الميلاد، اللغة، اختيارات الموافقة، وقت الموافقة، إصدار الإشعار، عنوان الإنترنت، ونوع المتصفح. لا نجمع الهوية الوطنية أو بيانات صحية أو بيانات حسّاسة أخرى.',
            'recipients' => 'المستلمون',
            'recipients_body' => 'فريق ديلاوة المشغّل لهذه الحملة، ومقدّمو خدمات الاستضافة والحماية. لا نبيع بياناتك.',
            'transfer' => 'النقل عبر الحدود',
            'transfer_body' => 'قد تعمل المنصة على بنية تحتية خارج المملكة. يتم هذا النقل فقط بموافقتك الصريحة ولتشغيل هذه الحملة.',
            'retention' => 'مدة الاحتفاظ',
            'retention_body' => 'تُحفظ التسجيلات النشطة لمدة :days يومًا من الجمع ثم تُحذف. إذا سحبت موافقتك تُحذف المعرّفات الشخصية فورًا ويُزال سجل التدقيق خلال 30 يومًا.',
            'rights' => 'حقوقك',
            'rights_body' => 'يمكنك طلب الاطلاع أو التصحيح أو الإتلاف، وسحب الموافقة في أي وقت. سحب الموافقة بنفس سهولة التسجيل عبر نموذج السحب، أو عبر البريد :email. يمكنك أيضًا تقديم شكوى لدى سدايا.',
            'automated' => 'القرارات الآلية',
            'automated_body' => 'لا نتخذ قرارات آلية فقط تترتب عليها آثار نظامية أو مماثلة. تُستخدم سنة الميلاد فقط للتأكد من أن عمرك 18 سنة فأكثر.',
            'children' => 'الأطفال',
            'children_body' => 'هذا النموذج للبالغين. لا نُسجل من هم دون 18 عامًا عن علم.',
            'contact' => 'التواصل',
            'version' => 'إصدار الإشعار',
            'back' => 'العودة للتسجيل',
            'withdraw' => 'سحب الموافقة',
        ],
    ][$locale];
@endphp

<x-sa96-layout
    :locale="$locale"
    :dir="$dir"
    :next-locale="$nextLocale"
    :title="$copy['title']"
    :description="$copy['purposes_body']"
>
    <main class="flex flex-1 flex-col">
        <article class="rounded-[1.75rem] bg-white/95 p-5 shadow-2xl shadow-black/20">
            <h1 class="text-2xl leading-tight text-slate-950">{{ $copy['title'] }}</h1>

            <div class="mt-5 flex flex-col gap-5 text-sm leading-7 text-slate-700">
                <section>
                    <h2 class="font-semibold text-slate-950">{{ $copy['controller'] }}</h2>
                    <p>{{ $controllerLegalName }} — {{ $privacyEmail }}</p>
                </section>
                <section>
                    <h2 class="font-semibold text-slate-950">{{ $copy['purposes'] }}</h2>
                    <p>{{ $copy['purposes_body'] }}</p>
                </section>
                <section>
                    <h2 class="font-semibold text-slate-950">{{ $copy['basis'] }}</h2>
                    <p>{{ $copy['basis_body'] }}</p>
                </section>
                <section>
                    <h2 class="font-semibold text-slate-950">{{ $copy['categories'] }}</h2>
                    <p>{{ $copy['categories_body'] }}</p>
                </section>
                <section>
                    <h2 class="font-semibold text-slate-950">{{ $copy['recipients'] }}</h2>
                    <p>{{ $copy['recipients_body'] }}</p>
                </section>
                <section>
                    <h2 class="font-semibold text-slate-950">{{ $copy['transfer'] }}</h2>
                    <p>{{ $copy['transfer_body'] }}</p>
                </section>
                <section>
                    <h2 class="font-semibold text-slate-950">{{ $copy['retention'] }}</h2>
                    <p>{{ str_replace(':days', (string) $retentionDays, $copy['retention_body']) }}</p>
                </section>
                <section>
                    <h2 class="font-semibold text-slate-950">{{ $copy['rights'] }}</h2>
                    <p>{{ str_replace(':email', $privacyEmail, $copy['rights_body']) }}</p>
                    <p>
                        <a href="{{ $sdaiaUrl }}" class="font-semibold text-[#006C35] underline underline-offset-2" rel="noopener noreferrer">SDAIA</a>
                    </p>
                </section>
                <section>
                    <h2 class="font-semibold text-slate-950">{{ $copy['automated'] }}</h2>
                    <p>{{ $copy['automated_body'] }}</p>
                </section>
                <section>
                    <h2 class="font-semibold text-slate-950">{{ $copy['children'] }}</h2>
                    <p>{{ $copy['children_body'] }}</p>
                </section>
                <section>
                    <h2 class="font-semibold text-slate-950">{{ $copy['contact'] }}</h2>
                    <p>{{ $privacyEmail }}</p>
                    <p>{{ $copy['version'] }}: {{ $noticeVersion }}</p>
                </section>
            </div>

            <div class="mt-6 flex flex-col gap-3">
                <a href="{{ route('sa96.create', ['lang' => $locale]) }}" class="inline-flex min-h-12 items-center justify-center rounded-2xl bg-[#006C35] px-4 text-base font-semibold text-white">{{ $copy['back'] }}</a>
                <a href="{{ route('sa96.withdraw', ['lang' => $locale]) }}" class="inline-flex min-h-12 items-center justify-center rounded-2xl bg-white px-4 text-base font-semibold text-slate-800 ring-1 ring-slate-200">{{ $copy['withdraw'] }}</a>
            </div>
        </article>
    </main>
</x-sa96-layout>
