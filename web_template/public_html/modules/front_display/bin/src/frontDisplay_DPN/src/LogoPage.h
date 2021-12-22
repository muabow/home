#ifndef __LOGO_PAGE_H__
#define __LOGO_PAGE_H__


#include <sys/stat.h>

#include <avmsapi/GFX.h>
#include <avmsapi/OLED.h>

#include "AbstractPage.h"


using namespace std;


inline bool isFileExist (const std::string& name) {
	struct stat buffer;   
	return (stat (name.c_str(), &buffer) == 0); 
}

class Logo {
public:
	Logo(const string path, const string filename) throw (const char*) {
		string logoFile = path + "/" + filename + ".bmp";
		if (isFileExist(logoFile) == false) {
			logoFile = path + "/resource/Logo_interm.bmp";
		}
		printf("[Logo File]\n");
		printf("Filename = %s\n", logoFile.c_str());
		ifstream bitmapStream(logoFile.c_str(), ios::binary);
		
		char buf[128] = {0,};
		bitmapStream.read(buf, 6);
		
		if (memcmp(buf, "BM", 2) != 0) {
			bitmapStream.close();
			throw "Unable to read Logo ile";
		}

		int32_t dataOffset = 0;
		bitmapStream.seekg(0x0A, ios::beg);
		bitmapStream.read((char*)&dataOffset, sizeof(int32_t));
		_bih.clear();
		
		
		bitmapStream.seekg(0x0E, ios::beg);
		bitmapStream.read((char*)&_bih, sizeof(_bih));
		
		printf("Width = %u\n", _bih.width);
		printf("Height = %u\n", _bih.height);
		printf("BitCount = %u\n", _bih.bit_count);
//		printf("Size = %u\n", _bih.size_image);	// 파일을 저장한 Tool 에 따라 0으로 설정되는 경우도 있음
		printf("DataOffset = %d\n", dataOffset);

		// OLED 보다 크거나, 단색이 아닌경우 처리 불가
		if (_bih.width > MAX_WIDTH || _bih.height > MAX_HEIGHT || _bih.bit_count != 1) {
			bitmapStream.close();
			throw "Invalid Format (Bitmap, 128 x 28 x 1bit, width = 8x)";
		}

		const int32_t length = MAX_WIDTH * MAX_WIDTH / 8;		// bpp:1
		if ( (_data = (uint8_t*)malloc(length)) ) {
			memset(_data, 0, length);
		}
		else {
			bitmapStream.close();
			throw "Unable to create buffer";
		}

		// Read Image Data
		int32_t alighedWidth4 = (((_bih.width - 1) / (8 * 4)) + 1) * 4;		// 4 Byte Align
		int32_t alighedWidth1 = (((_bih.width - 1) / 8) + 1);				// 1 Byte Align
		printf("AlighedWidth4 = %d\n", alighedWidth4);
		printf("AlighedWidth1 = %d\n", alighedWidth1);


		bitmapStream.seekg(dataOffset, ios::beg);
		for (int i = _bih.height-1 ; i >= 0; i--) {
			memset(buf, sizeof(buf), 0);
//			printf("[%d]\n", i);

			bitmapStream.read(buf, alighedWidth4);	// 1bit BMP 파일은 4Byte Align 으로 저장되어 있음
			const int32_t writeOffset = i * alighedWidth1;	// data 는 1Byte Align 으로 저장함
//			printf("writeOffset  = %d\n", writeOffset );
			memcpy(_data + writeOffset, buf, alighedWidth1);
		}

		bitmapStream.close();
		printf("\n");
		return;
	};
	virtual ~Logo(void) {
		if(_data) {
			free(_data);
			_data = NULL;
		}
	};

	uint32_t width() { return _bih.width; };
	uint32_t height() { return _bih.height; };
	uint8_t* data() { return _data; };

private:
	static const uint32_t MAX_WIDTH = 128;			///< Logo Width
	static const uint32_t MAX_HEIGHT = 28;			///< Logo Height

	struct bitmap_information_header
	{
	   uint32_t size;
	   uint32_t width;
	   uint32_t height;
	   uint16_t planes;
	   uint16_t bit_count;
	   uint32_t compression;
	   uint32_t size_image;
	   uint32_t x_pels_per_meter;
	   uint32_t y_pels_per_meter;
	   uint32_t clr_used;
	   uint32_t clr_important;
	
	   unsigned int struct_size() const
	   {
		  return sizeof(size			) +
				 sizeof(width			) +
				 sizeof(height			) +
				 sizeof(planes			) +
				 sizeof(bit_count		) +
				 sizeof(compression 	) +
				 sizeof(size_image		) +
				 sizeof(x_pels_per_meter) +
				 sizeof(y_pels_per_meter) +
				 sizeof(clr_used		) +
				 sizeof(clr_important	) ;
	   }
	
	   void clear()
	   {
		  std::memset(this, 0x00, sizeof(bitmap_information_header));
	   }
	};


	bitmap_information_header _bih;
	uint8_t *_data;
};

class LogoPage: public AbstractPage {
private:
	void init() throw (const char*);

public:
	LogoPage(const string path, const string filename, const string title1, const string interface1) throw (const char*);
	LogoPage(const string path, const string filename, const string title1, const string interface1, const string title2, const string interface2, const string titleBonding = "", const string interfaceBonding = "") throw (const char*);
	virtual ~LogoPage() throw (const char*) {};

	virtual void draw(OLED* oled);
	// Volume Control Device {
	uint32_t getVolume() {
		uint32_t result = 0;
		_mutex.lock();
		result = _volume;
		_mutex.unlock();
		return result;
	};
	
	void setVolume(uint32_t volume) {
		_mutex.lock();
		_volume = volume;
		_mutex.unlock();
	};
	
	bool isVolumeControlDevice() {
		bool result = false;
		_mutex.lock();
		result = _isVolumeControlDevice;
		_mutex.unlock();
		return result;
	};
	// Volume Control Device }
	
private:

	static const uint32_t BODY_WIDTH = 128;			///< Body Width
	static const uint32_t BODY_HEIGHT = 64;			///< Body Height

	static const uint32_t MAX_BUF = 128;
	static const string CONFIG_FILE_NAME;		///< Device Name, Version, Type 정보가 있는 설정 파일 명

	Canvas1 _bodyCanvas;		///< Body Canvas

	Logo _logo;
	const string _path;
	const string _title1;
	const string _interface1;
	const string _title2;
	const string _interface2;
	const string _titleBonding;
	const string _interfaceBonding;
	const int32_t _interfaceCount;
	string _name;
	string _version;

	// Volume Control Device {
	Mutex _mutex;
	string _type;
	
	bool _isVolumeControlDevice;
	uint32_t _volume;
	// Volume Control Device }
};

#endif	// __LOGO_PAGE_H__

