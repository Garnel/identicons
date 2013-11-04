from PIL import Image, ImageDraw
import colorsys
import random

def randcolor():
    h = random.uniform(0.02, 0.31)*random.choice([1,2,3])
    l = random.uniform(0.3, 0.8)
    s = random.uniform(0.3, 0.8)
    rgb = colorsys.hls_to_rgb(h, l, s)
    return (int(rgb[0]*256), int(rgb[1]*256), int(rgb[2]*256))

edge = 30
size = (edge, edge)
im = Image.new('RGB', size, 'white')
draw = ImageDraw.Draw(im)

for i in xrange(edge):
    for j in xrange(edge):
        posl = (i, j)
        draw.rectangle((posl, posl), fill=randcolor())

del draw
im.show()