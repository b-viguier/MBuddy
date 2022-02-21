import ui
import motif


class VoicePicker(ui.View):
    def __init__(self, *args, **kwargs):
        super(VoicePicker, self).__init__(args, kwargs)
        self.initial_voice_ids = None
        self.current_voice = ('', '', '')
        self.current_path = ('', '')
        self.on_changed = lambda *args: None

    @staticmethod
    def load_view(on_changed=None, current=None):
        instance = ui.load_view()
        instance.initial_voice_ids = current if current else instance.initial_voice_ids
        if instance.initial_voice_ids:
            instance.current_voice = motif.id2voices[instance.initial_voice_ids[0]][instance.initial_voice_ids[1]][instance.initial_voice_ids[2]]
            instance.current_path = instance.current_voice
            instance.__fill_categories()
            instance.__fill_sub_categories()
            instance.__fill_programs()
        instance.on_changed = on_changed if on_changed else instance.on_changed
        return instance

    def did_load(self):
        self.__fill_categories()
        self['categories_view'].data_source.action = self.on_category_selected
        self['categories_view'].data_source.accessory_action = self.on_category_selected
        self['sub_categories_view'].data_source.action = self.on_sub_category_selected
        self['sub_categories_view'].data_source.accessory_action = self.on_sub_category_selected
        self['programs_view'].data_source.action = self.on_program_selected
        self['programs_view'].data_source.accessory_action = self.on_program_selected
        self['cancel_button'].action = self.on_cancel_clicked
        self['ok_button'].action = self.on_ok_clicked

    def on_category_selected(self, sender):
        self.current_path = (self.__current_item('categories_view'), '')
        self.__fill_sub_categories()
        self.__fill_programs()
        self['programs_view'].data_source.items = []

    def on_sub_category_selected(self, sender):
        self.current_path = (self.current_path[0], self.__current_item('sub_categories_view'), '', '')
        self.__fill_programs()

    def on_program_selected(self, sender):
        category = self.current_path[0]
        sub_category = self.current_path[1]
        program = self.__current_item('programs_view')

        if category is not None and sub_category is not None and program is not None:
            self.current_voice = (category, sub_category, program)
            self.__fill_categories()
            self.__fill_sub_categories()
            self.__fill_programs()
            self.on_changed((motif.voices2id[category][sub_category][program]))

    def on_ok_clicked(self, button):
        self.close()

    def on_cancel_clicked(self, button):
        self.on_changed(self.initial_voice_ids)
        self.close()

    def __current_item(self, view_name):
        return self[view_name].data_source.items[self[view_name].data_source.selected_row]['title'] if self[view_name].data_source.selected_row is not None else None

    def __fill_categories(self):
        self['categories_view'].data_source.items = [{
            'title': cat,
            'accessory_type': 'disclosure_indicator' if cat != self.current_voice[0] else 'detail_disclosure_button'
        } for cat in motif.voices2id.keys()]

    def __fill_sub_categories(self):
        category = self.current_path[0]
        display_indicator = category == self.current_voice[0]
        self['sub_categories_view'].data_source.items = [{
            'title': sub_cat,
            'accessory_type': 'disclosure_indicator' if not display_indicator or sub_cat != self.current_voice[1] else 'detail_disclosure_button'
        } for sub_cat in motif.voices2id[category].keys()]

    def __fill_programs(self):
        category = self.current_path[0]
        sub_category = self.current_path[1]
        display_indicator = category == self.current_voice[0] and sub_category == self.current_voice[1]
        self['programs_view'].data_source.items = [{
            'title': prg,
            'accessory_type': 'none' if not display_indicator or prg != self.current_voice[2] else 'detail_button'
        } for prg in motif.voices2id[category][sub_category].keys()] if sub_category != '' else []


if __name__ == '__main__':
    def on_changed(voice):
        print(voice)
    v = VoicePicker.load_view(on_changed=on_changed, current=(63, 5, 98))
    v.present('sheet')
