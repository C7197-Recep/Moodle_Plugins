NEWMODULEDEN URETILEN KURS BAŞLANGIÇ-BİTİŞİNİN ÖĞRETMENLERE EPOSTA OLARAK BİLDİRİLMESİ PLUGİNİ

Bu plugin moodle için template olarak sunulan Newmodule plugininden üretildi.

Newmodule view.php içinde deprecate edilmiş 2 kod satırı gizlendi. 

Birisinin karşılığı bulunamadı, diğerinin bulundu.

Bu bir kurs aktivitesi plugini. 

Yeni aktivite pluginleri üretmek için bu plugin modifiye edilebilir.

KULLANIMI

*Kursun başına ve sonuna bu plugini kur. Baştakine Welcome, sondakine Congradulations
gibi isimler verilebilir.

*Bu bu pluginleri diğer aktivitelerle restrictionlarla bağla. Böylece newmodule tıklayıp
öğretmene bilgi vermeden kursa başlayamasın.

*İlk başa koyduğumuz aktiviteye öğrenci tıkladığında bir bilgilendirme metniyle karşılaşıyor
ve kursun bütün öğretmenlerine öğrenci bilgileri ile öğrencinin kursa başladığı bilgisi
eposta olarak gidiyor. Öğrenci bu aktiviteye yeniden tıklarsa eposta tekrar gitmiyor.
Çünkü bu epostanın gittiği veritabanına kaydediliyor, tekrar göndermiyor.

*Son aktiviteye tıklanıldığında, eğer öğrenci statüsü kursu bitirdi şeklinde ise
öğretmenlere öğrencinin bilgileri ve kursu bitirdiği bilgisi gidiyor.

KODLAMA KONUSUNDA BİLGİLER

*Aktivitenin tıklandığında completed statüsüne kavuşması için 

lib.php içinde newmodule_supports fonksiyonu altındaki 

 case FEATURE_COMPLETION_TRACKS_VIEWS:return true;

kodunun eklenmesi gerekti.

view.php içinde şu kodun eklenmesi gerekti.

$completion = new completion_info($course);
$completion->set_module_viewed($cm);
$completion->update_state($cm, COMPLETION_COMPLETE, $USER->id);

*Uygulamaya tıklandığında veritabanına kayıt ve öğrenci bilgilerinin çekilmesi
için yapılan işlemlerin tamamı view.php içinde mevcut.



ORIJINAL README

The following steps should get you up and running with
this module template code.

* DO NOT PANIC!

* Unzip the archive and read this file

* Rename the NEWMODULE/ folder to the name of your module (eg "widget").
  The module folder MUST be lower case. You should check the CVS contrib
  area at http://cvs.moodle.org/contrib/plugins/mod/ to make sure that
  your name is not already used by an other module.

* Edit all the files in this directory and its subdirectories and change
  all the instances of the string "newmodule" to your module name
  (eg "widget"). If you are using Linux, you can use the following command
  $ find . -type f -exec sed -i 's/newmodule/widget/g' {} \;

* Rename the file lang/en_utf8/newmodule.php to lang/en_utf8/widget.php
  where "widget" is the name of your module

* Place the widget folder into the /mod folder of the moodle
  directory.

* Go to Settings > Site Administration > Development > XMLDB editor
  and modify the module's tables.

* Modify version.php and set the initial version of you module.

* Visit Settings > Site Administration > Notifications, you should find
  the module's tables successfully created

* Go to Site Administration > Plugins > Activity modules > Manage activities
  and you should find that this newmodule has been added to the list of
  installed modules.

* You may now proceed to run your own code in an attempt to develop
  your module. You will probably want to modify mod_form.php and view.php
  as a first step. Check db/access.php to add capabilities.

We encourage you to share your code and experience - visit http://moodle.org

Good luck!
