/* French initialisation for the jQuery time picker plugin. */

/* Written by Bernd Plagge (bplagge@choicenet.ne.jp),

              Francois Gelinas (frank@fgelinas.com) */

jQuery(function($){

    $.timepicker.regional['fr'] = {

                hourText: 'Heures',

                minuteText: 'Minutes',

                amPmText: ['AM', 'PM'],

                closeButtonText: 'Fermer',

                nowButtonText: 'Maintenant',

                deselectButtonText: 'D챕s챕lectionner' }

    $.timepicker.setDefaults($.timepicker.regional['fr']);

});
