YUI.add('moodle-format_simple-section', function (Y, NAME) {

/**
 * Section
 *
 * @class M.format_simple.section
 * @constructor
 *
 */

var SIMPLESECTION = function() {
  SIMPLESECTION.superclass.constructor.apply(this, arguments);
};

M.format_simple = M.format_simple || {};
M.format_simple.section = M.format_simple.section || {};

M.format_simple.section.init_section = function(params) {
  return new SIMPLESECTION(params);
};

Y.extend(SIMPLESECTION, Y.Base, {
    
  initializer : function(params) {
    
    Y.all('.content--editing').each(function (content) {
      var toggle_link = Y.Node.create("<a href=\"#\" class=\"content-toggle\" title=\"Click to expand or collapse\" data-text-swap=\"Hide\">Show</a>");
      content.insert(toggle_link, 'before');
    });
    
    Y.all('.section .content-toggle').on('click', this.toggle_content, this);
    
    if (params && params.sectionid) {
      this.scroll_to_section_and_reveal(params.sectionid);
    }
  },
  
  scroll_to_section_and_reveal : function(sectionid) {
    /* scroll window to current section */
    var sectionelem = Y.one('#section-' + sectionid);
    
    if (sectionelem) {
      /* calculate the outer height of the fixed navbar and subtract */
      var scrollpos = sectionelem.getY() -
                      (Y.one('.navbar').get('offsetHeight') +
                      parseFloat(Y.one('.navbar').getComputedStyle('marginTop')) +
                      parseFloat(Y.one('.navbar').getComputedStyle('marginBottom')));
      
      var anim = new Y.Anim({
        duration: 0.5,
        node: 'win',
        easing: 'easeBoth',
        to: {
          scroll: [0, scrollpos]
        }
      });
      
      anim.run();
      anim.on('end',function() {
        sectionelem.one('.content-toggle').simulate('click');
      });
    }
  },
  
  toggle_content : function(e) {
    var link = e.target;
    link.next('.content--editing').toggleView(0, function() {
      // swap link text with its toggled state version
      if (link.get('text') === link.getData('text-swap')) {
        link.set('text', link.getData('text-original'));
      } else {
        link.setData('text-original', link.get('text'));
      link.set('text', link.getData('text-swap'));
      }
    });
    
    e.preventDefault();
  }
},{
  NAME: 'simple_section',
    ATTRS: {
    }
});

}, '@VERSION@', {"requires": ["base", "node", "anim"]});
