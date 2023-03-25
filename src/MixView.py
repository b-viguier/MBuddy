import ui
from PartEditor import PartEditor
import State


class MixView(ui.View):
    def __init__(self, state, *args, **kwargs):
        super(MixView, self).__init__(args, kwargs)
        self.background_color = (0, 0, 0)
        for channel in range(0, 16):
            row = channel % 8
            col = int(channel / 8)
            part = PartEditor.load_view(state=state.part(channel))
            self.add_subview(part)
            part.y = row * (5 + part.height) + 30
            part.x = round((1024 - 2 * part.width - 5) / 2) + col * (part.width + 5)


if __name__ == '__main__':
    v = MixView(State.SongState.from_default())
    v.present(style='fullscreen', orientations='landscape', hide_close_button=False, hide_title_bar=True)
