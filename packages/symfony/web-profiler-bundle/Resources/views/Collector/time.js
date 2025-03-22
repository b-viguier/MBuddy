'use strict';

function _readOnlyError(r) { throw new TypeError('"' + r + '" is read-only'); }
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
var TimelineEngine = /*#__PURE__*/function () {
  /**
   * @param  {Theme} theme
   * @param  {Renderer} renderer
   * @param  {Legend} legend
   * @param  {Element} threshold
   * @param  {Object} request
   * @param  {Number} eventHeight
   * @param  {Number} horizontalMargin
   */
  function TimelineEngine(theme, renderer, legend, threshold, request) {
    var eventHeight = arguments.length > 5 && arguments[5] !== undefined ? arguments[5] : 36;
    var horizontalMargin = arguments.length > 6 && arguments[6] !== undefined ? arguments[6] : 10;
    _classCallCheck(this, TimelineEngine);
    this.theme = theme;
    this.renderer = renderer;
    this.legend = legend;
    this.threshold = threshold;
    this.request = request;
    this.scale = renderer.width / request.end;
    this.eventHeight = eventHeight;
    this.horizontalMargin = horizontalMargin;
    this.labelY = Math.round(this.eventHeight * 0.48);
    this.periodY = Math.round(this.eventHeight * 0.66);
    this.FqcnMatcher = /\\([^\\]+)$/i;
    this.origin = null;
    this.createEventElements = this.createEventElements.bind(this);
    this.createBackground = this.createBackground.bind(this);
    this.createPeriod = this.createPeriod.bind(this);
    this.render = this.render.bind(this);
    this.renderEvent = this.renderEvent.bind(this);
    this.renderPeriod = this.renderPeriod.bind(this);
    this.onResize = this.onResize.bind(this);
    this.isActive = this.isActive.bind(this);
    this.threshold.addEventListener('change', this.render);
    this.legend.addEventListener('change', this.render);
    window.addEventListener('resize', this.onResize);
    this.createElements();
    this.render();
  }
  return _createClass(TimelineEngine, [{
    key: "onResize",
    value: function onResize() {
      this.renderer.measure();
      this.setScale(this.renderer.width / this.request.end);
    }
  }, {
    key: "setScale",
    value: function setScale(scale) {
      if (scale !== this.scale) {
        this.scale = scale;
        this.render();
      }
    }
  }, {
    key: "createElements",
    value: function createElements() {
      this.origin = this.renderer.setFullVerticalLine(this.createBorder(), 0);
      this.renderer.add(this.origin);
      this.request.events.filter(function (event) {
        return event.category === 'section';
      }).map(this.createBackground).forEach(this.renderer.add);
      this.request.events.map(this.createEventElements).forEach(this.renderer.add);
    }
  }, {
    key: "createBackground",
    value: function createBackground(event) {
      var subrequest = event.name === '__section__.child';
      var background = this.renderer.create('rect', subrequest ? 'timeline-subrequest' : 'timeline-border');
      event.elements = Object.assign(event.elements || {}, {
        background: background
      });
      return background;
    }
  }, {
    key: "createEventElements",
    value: function createEventElements(event) {
      var _this = this;
      var name = event.name,
        category = event.category,
        duration = event.duration,
        memory = event.memory,
        periods = event.periods;
      var border = this.renderer.setFullHorizontalLine(this.createBorder(), 0);
      var lines = periods.map(function (period) {
        return _this.createPeriod(period, category);
      });
      var label = this.createLabel(this.getShortName(name), duration, memory, periods[0]);
      var title = this.renderer.createTitle(name);
      var group = this.renderer.group([title, border, label].concat(lines), this.theme.getCategoryColor(event.category));
      event.elements = Object.assign(event.elements || {}, {
        group: group,
        label: label,
        border: border
      });
      this.legend.add(event.category);
      return group;
    }
  }, {
    key: "createLabel",
    value: function createLabel(name, duration, memory, period) {
      var label = this.renderer.createText(name, period.start * this.scale, this.labelY, 'timeline-label');
      var sublabel = this.renderer.createTspan("  ".concat(duration, " ms / ").concat(memory, " MiB"), 'timeline-sublabel');
      label.appendChild(sublabel);
      return label;
    }
  }, {
    key: "createPeriod",
    value: function createPeriod(period, category) {
      var timeline = this.renderer.createPath(null, 'timeline-period', this.theme.getCategoryColor(category));
      period.draw = category === 'section' ? this.renderer.setSectionLine : this.renderer.setPeriodLine;
      period.elements = Object.assign(period.elements || {}, {
        timeline: timeline
      });
      return timeline;
    }
  }, {
    key: "createBorder",
    value: function createBorder() {
      return this.renderer.createPath(null, 'timeline-border');
    }
  }, {
    key: "isActive",
    value: function isActive(event) {
      var duration = event.duration,
        category = event.category;
      return duration >= this.threshold.value && this.legend.isActive(category);
    }
  }, {
    key: "render",
    value: function render() {
      var _this2 = this;
      var events = this.request.events.filter(this.isActive);
      var width = this.renderer.width + this.horizontalMargin * 2;
      var height = this.eventHeight * events.length;

      // Set view box
      this.renderer.setViewBox(-this.horizontalMargin, 0, width, height);

      // Show 0ms origin
      this.renderer.setFullVerticalLine(this.origin, 0);

      // Render all events
      this.request.events.forEach(function (event) {
        return _this2.renderEvent(event, events.indexOf(event));
      });
    }
  }, {
    key: "renderEvent",
    value: function renderEvent(event, index) {
      var name = event.name,
        category = event.category,
        duration = event.duration,
        memory = event.memory,
        periods = event.periods,
        elements = event.elements;
      var group = elements.group,
        label = elements.label,
        border = elements.border,
        background = elements.background;
      var visible = index >= 0;
      group.setAttribute('visibility', visible ? 'visible' : 'hidden');
      if (background) {
        background.setAttribute('visibility', visible ? 'visible' : 'hidden');
        if (visible) {
          var _this$getEventLimits = this.getEventLimits(event),
            _this$getEventLimits2 = _slicedToArray(_this$getEventLimits, 2),
            min = _this$getEventLimits2[0],
            max = _this$getEventLimits2[1];
          this.renderer.setFullRectangle(background, min * this.scale, max * this.scale);
        }
      }
      if (visible) {
        // Position the group
        group.setAttribute('transform', "translate(0, ".concat(index * this.eventHeight, ")"));

        // Update top border
        this.renderer.setFullHorizontalLine(border, 0);

        // render label and ensure it doesn't escape the viewport
        this.renderLabel(label, event);

        // Update periods
        periods.forEach(this.renderPeriod);
      }
    }
  }, {
    key: "renderLabel",
    value: function renderLabel(label, event) {
      var width = this.getLabelWidth(label);
      var _this$getEventLimits3 = this.getEventLimits(event),
        _this$getEventLimits4 = _slicedToArray(_this$getEventLimits3, 2),
        min = _this$getEventLimits4[0],
        max = _this$getEventLimits4[1];
      var alignLeft = min * this.scale + width <= this.renderer.width;
      label.setAttribute('x', (alignLeft ? min : max) * this.scale);
      label.setAttribute('text-anchor', alignLeft ? 'start' : 'end');
    }
  }, {
    key: "renderPeriod",
    value: function renderPeriod(period) {
      var elements = period.elements,
        start = period.start,
        duration = period.duration;
      period.draw(elements.timeline, start * this.scale, this.periodY, Math.max(duration * this.scale, 1));
    }
  }, {
    key: "getLabelWidth",
    value: function getLabelWidth(label) {
      if (typeof label.width === 'undefined') {
        label.width = label.getBBox().width;
      }
      return label.width;
    }
  }, {
    key: "getEventLimits",
    value: function getEventLimits(event) {
      if (typeof event.limits === 'undefined') {
        var periods = event.periods;
        event.limits = [periods[0].start, periods[periods.length - 1].end];
      }
      return event.limits;
    }
  }, {
    key: "getShortName",
    value: function getShortName(name) {
      var matches = this.FqcnMatcher.exec(name);
      if (matches) {
        return matches[1];
      }
      return name;
    }
  }]);
}();
var Legend = /*#__PURE__*/function () {
  function Legend(element, theme) {
    _classCallCheck(this, Legend);
    this.element = element;
    this.theme = theme;
    this.toggle = this.toggle.bind(this);
    this.createCategory = this.createCategory.bind(this);
    this.categories = [];
    this.theme.getDefaultCategories().forEach(this.createCategory);
  }
  return _createClass(Legend, [{
    key: "add",
    value: function add(category) {
      this.get(category).classList.add('present');
    }
  }, {
    key: "createCategory",
    value: function createCategory(category) {
      var element = document.createElement('button');
      element.className = "timeline-category active";
      element.style.borderColor = this.theme.getCategoryColor(category);
      element.innerText = category;
      element.value = category;
      element.type = 'button';
      element.addEventListener('click', this.toggle);
      this.element.appendChild(element);
      this.categories.push(element);
      return element;
    }
  }, {
    key: "toggle",
    value: function toggle(event) {
      event.target.classList.toggle('active');
      this.emit('change');
    }
  }, {
    key: "isActive",
    value: function isActive(category) {
      return this.get(category).classList.contains('active');
    }
  }, {
    key: "get",
    value: function get(category) {
      return this.categories.find(function (element) {
        return element.value === category;
      }) || this.createCategory(category);
    }
  }, {
    key: "emit",
    value: function emit(name) {
      this.element.dispatchEvent(new Event(name));
    }
  }, {
    key: "addEventListener",
    value: function addEventListener(name, callback) {
      this.element.addEventListener(name, callback);
    }
  }, {
    key: "removeEventListener",
    value: function removeEventListener(name, callback) {
      this.element.removeEventListener(name, callback);
    }
  }]);
}();
var SvgRenderer = /*#__PURE__*/function () {
  /**
   * @param  {SVGElement} element
   */
  function SvgRenderer(element) {
    _classCallCheck(this, SvgRenderer);
    this.ns = 'http://www.w3.org/2000/svg';
    this.width = null;
    this.viewBox = {};
    this.element = element;
    this.add = this.add.bind(this);
    this.setViewBox(0, 0, 0, 0);
    this.measure();
  }
  return _createClass(SvgRenderer, [{
    key: "setViewBox",
    value: function setViewBox(x, y, width, height) {
      this.viewBox = {
        x: x,
        y: y,
        width: width,
        height: height
      };
      this.element.setAttribute('viewBox', "".concat(x, " ").concat(y, " ").concat(width, " ").concat(height));
    }
  }, {
    key: "measure",
    value: function measure() {
      this.width = this.element.getBoundingClientRect().width;
    }
  }, {
    key: "add",
    value: function add(element) {
      this.element.appendChild(element);
    }
  }, {
    key: "group",
    value: function group(elements, className) {
      var group = this.create('g', className);
      elements.forEach(function (element) {
        return group.appendChild(element);
      });
      return group;
    }
  }, {
    key: "setHorizontalLine",
    value: function setHorizontalLine(element, x, y, width) {
      element.setAttribute('d', "M".concat(x, ",").concat(y, " h").concat(width));
      return element;
    }
  }, {
    key: "setVerticalLine",
    value: function setVerticalLine(element, x, y, height) {
      element.setAttribute('d', "M".concat(x, ",").concat(y, " v").concat(height));
      return element;
    }
  }, {
    key: "setFullHorizontalLine",
    value: function setFullHorizontalLine(element, y) {
      return this.setHorizontalLine(element, this.viewBox.x, y, this.viewBox.width);
    }
  }, {
    key: "setFullVerticalLine",
    value: function setFullVerticalLine(element, x) {
      return this.setVerticalLine(element, x, this.viewBox.y, this.viewBox.height);
    }
  }, {
    key: "setFullRectangle",
    value: function setFullRectangle(element, min, max) {
      element.setAttribute('x', min);
      element.setAttribute('y', this.viewBox.y);
      element.setAttribute('width', max - min);
      element.setAttribute('height', this.viewBox.height);
    }
  }, {
    key: "setSectionLine",
    value: function setSectionLine(element, x, y, width) {
      var height = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : 4;
      var markerSize = arguments.length > 5 && arguments[5] !== undefined ? arguments[5] : 6;
      var totalHeight = height + markerSize;
      var maxMarkerWidth = Math.min(markerSize, width / 2);
      var widthWithoutMarker = Math.max(0, width - maxMarkerWidth * 2);
      element.setAttribute('d', "M".concat(x, ",").concat(y + totalHeight, " v").concat(-totalHeight, " h").concat(width, " v").concat(totalHeight, " l").concat(-maxMarkerWidth, " ").concat(-markerSize, " h").concat(-widthWithoutMarker, " Z"));
    }
  }, {
    key: "setPeriodLine",
    value: function setPeriodLine(element, x, y, width) {
      var height = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : 4;
      var markerWidth = arguments.length > 5 && arguments[5] !== undefined ? arguments[5] : 2;
      var markerHeight = arguments.length > 6 && arguments[6] !== undefined ? arguments[6] : 4;
      var totalHeight = height + markerHeight;
      var maxMarkerWidth = Math.min(markerWidth, width);
      element.setAttribute('d', "M".concat(x + maxMarkerWidth, ",").concat(y + totalHeight, " h").concat(-maxMarkerWidth, " v").concat(-totalHeight, " h").concat(width, " v").concat(height, " h").concat(maxMarkerWidth - width, "Z"));
    }
  }, {
    key: "createText",
    value: function createText(content, x, y, className) {
      var element = this.create('text', className);
      element.setAttribute('x', x);
      element.setAttribute('y', y);
      element.textContent = content;
      return element;
    }
  }, {
    key: "createTspan",
    value: function createTspan(content, className) {
      var element = this.create('tspan', className);
      element.textContent = content;
      return element;
    }
  }, {
    key: "createTitle",
    value: function createTitle(content) {
      var element = this.create('title');
      element.textContent = content;
      return element;
    }
  }, {
    key: "createPath",
    value: function createPath() {
      var path = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
      var className = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
      var color = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
      var element = this.create('path', className);
      if (path) {
        element.setAttribute('d', path);
      }
      if (color) {
        element.setAttribute('fill', color);
      }
      return element;
    }
  }, {
    key: "create",
    value: function create(name) {
      var className = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
      var element = document.createElementNS(this.ns, name);
      if (className) {
        element.setAttribute('class', className);
      }
      return element;
    }
  }]);
}();
var Theme = /*#__PURE__*/function () {
  function Theme(element) {
    _classCallCheck(this, Theme);
    this.reservedCategoryColors = {
      'default': '#777',
      'section': '#999',
      'event_listener': '#00b8f5',
      'template': '#66cc00',
      'doctrine': '#ff6633',
      'messenger_middleware': '#bdb81e',
      'controller.argument_value_resolver': '#8c5de6',
      'http_client': '#ffa333'
    };
    this.customCategoryColors = ['#dbab09',
    // dark yellow
    '#ea4aaa',
    // pink
    '#964b00',
    // brown
    '#22863a',
    // dark green
    '#0366d6',
    // dark blue
    '#17a2b8' // teal
    ];
    this.getCategoryColor = this.getCategoryColor.bind(this);
    this.getDefaultCategories = this.getDefaultCategories.bind(this);
  }
  return _createClass(Theme, [{
    key: "getDefaultCategories",
    value: function getDefaultCategories() {
      return Object.keys(this.reservedCategoryColors);
    }
  }, {
    key: "getCategoryColor",
    value: function getCategoryColor(category) {
      return this.reservedCategoryColors[category] || this.getRandomColor(category);
    }
  }, {
    key: "getRandomColor",
    value: function getRandomColor(category) {
      // instead of pure randomness, colors are assigned deterministically based on the
      // category name, to ensure that each custom category always displays the same color
      return this.customCategoryColors[this.hash(category) % this.customCategoryColors.length];
    }

    // copied from https://github.com/darkskyapp/string-hash
  }, {
    key: "hash",
    value: function hash(string) {
      var hash = 5381;
      var i = string.length;
      while (i) {
        hash = hash * 33 ^ string.charCodeAt(--i);
      }
      return hash >>> 0;
    }
  }]);
}();

