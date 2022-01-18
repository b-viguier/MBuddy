import ui
from PartEditor import PartEditor
import State


class MixView(ui.View):
    def __init__(self, state, *args, **kwargs):
        super(MixView, self).__init__(args, kwargs)
        self.background_color = (0, 0, 0)
        for i in range(0, 8):
            part = PartEditor.load_view(state=state.part(i))
            self.add_subview(part)
            part.y = i * (5 + part.height) + 30
            part.x = round((1024 - part.width) / 2)


if __name__ == '__main__':
    v = MixView(State.SongState.from_default())
    v.present(style='fullscreen', orientations='landscape', hide_close_button=False, hide_title_bar=True)
