#ifndef __BIT_MASK_H__
#define __BIT_MASK_H__


#include <stdint.h>
#include <cassert>
#include <cstring>	// memset

#include "BitMask32.h"
#include "IStringConvertable.h"


template <int32_t BYTES> class BitMask;
template <int32_t BYTES> BitMask<BYTES> operator&(const BitMask<BYTES>& m1, const BitMask<BYTES>& m2);
template <int32_t BYTES> BitMask<BYTES> operator|(const BitMask<BYTES>& m1, const BitMask<BYTES>& m2);
template <int32_t BYTES> BitMask<BYTES> operator-(const BitMask<BYTES>& m1, const BitMask<BYTES>& m2);
template <int32_t BYTES> BitMask<BYTES> operator^(const BitMask<BYTES>& m1, const BitMask<BYTES>& m2);
template <int32_t BYTES> BitMask<BYTES> operator~(const BitMask<BYTES>& m1);



template <int32_t BYTES>
class BitMask
	: public IStringConvertable 
{
public:
    BitMask();
    explicit BitMask(uint8_t value);
	explicit BitMask(uint16_t value);
	explicit BitMask(uint32_t value);
	explicit BitMask(uint64_t value);
	explicit BitMask(const void* value);

	BitMask(const BitMask& copy);

	virtual ~BitMask() {}

private:
	uint8_t _data[BYTES]; // __attribute__ ((aligned(4)));

protected:
	// impl. - IStringConvertable
	virtual void makeAsString(std::string& buf) const;

public:
	bool operator==(const BitMask<BYTES>& mask) const;
#if 0
	bool operator<(const BitMask<BYTES>& mask) const;
	bool operator>(const BitMask<BYTES>& mask) const;
	bool operator<=(const BitMask<BYTES>& mask) const;
	bool operator>=(const BitMask<BYTES>& mask) const;
#endif

	BitMask<BYTES>& operator=(const BitMask<BYTES>& mask);	
	BitMask<BYTES>& operator&=(const BitMask<BYTES>& mask);
	BitMask<BYTES>& operator|=(const BitMask<BYTES>& mask);
	BitMask<BYTES>& operator-=(const BitMask<BYTES>& mask);
	BitMask<BYTES>& operator^=(const BitMask<BYTES>& mask);

	friend BitMask<BYTES> operator& <> (const BitMask<BYTES>& m1, const BitMask<BYTES>& m2);
	friend BitMask<BYTES> operator| <> (const BitMask<BYTES>& m1, const BitMask<BYTES>& m2);
	friend BitMask<BYTES> operator- <> (const BitMask<BYTES>& m1, const BitMask<BYTES>& m2);
	friend BitMask<BYTES> operator^ <> (const BitMask<BYTES>& m1, const BitMask<BYTES>& m2);
	friend BitMask<BYTES> operator~ <> (const BitMask<BYTES>& m1);

	void set(int32_t index);
	void set(const BitMask& mask);
	void reset(int32_t index);
	void reset(const BitMask& mask);
	void setAll();
	void resetAll();

	bool isSet(int32_t index) const;
	bool isSetAll() const;
	bool isSetAll(const BitMask& mask) const;
	bool isSetAny(const BitMask& mask) const;
	bool isEmpty() const;
	int32_t length() const	{ return BYTES; }

	uint8_t asUint8() const;
	uint16_t asUint16() const;
	uint32_t asUint32() const;
	uint64_t asUint64() const;
	const uint8_t* asBytes() const;

	int32_t setCount() const;
};


template <int32_t BYTES>
inline void BitMask<BYTES>::makeAsString(std::string& buf) const
{
	char str[2 + BYTES * 8 + 1];
	str[0] = '0';
	str[1] = 'b';
	for (int32_t i = 0; i < BYTES * 8; i++) {
//		str[BYTES * 8 - 1 - i + 2] = isSet(i) ? '1' : '0';
		str[BYTES * 8 - 1 - i + 2] = isSet(i) ? '1' : '_';
	}
	str[2 + BYTES * 8] = '\0';

	buf = str;
}

template <int32_t BYTES>
inline BitMask<BYTES>::BitMask()
{
	assert(BYTES > 0);
	resetAll();
}

template <int32_t BYTES>
inline BitMask<BYTES>::BitMask(uint8_t value)
{
	assert(BYTES == 1);
	_data[0] = value;
}

template <int32_t BYTES>
inline BitMask<BYTES>::BitMask(uint16_t value)
{
	assert(BYTES == 2);
	value = (value);
	memcpy(_data, &value, sizeof(uint16_t));
}

template <int32_t BYTES>
inline BitMask<BYTES>::BitMask(uint32_t value)
{
	assert(BYTES == 4);
	value = (value);
	memcpy(_data, &value, sizeof(uint32_t));
}

template <int32_t BYTES>
inline BitMask<BYTES>::BitMask(uint64_t value)
{
	assert(BYTES == 8);
	value = (value);
	memcpy(_data, &value, sizeof(uint64_t));
}

template <int32_t BYTES>
inline BitMask<BYTES>::BitMask(const void* value)
{
	memcpy(_data, &value, BYTES);
}

template <int32_t BYTES>
inline BitMask<BYTES>::BitMask(const BitMask<BYTES>& copy)
{
	memcpy(_data, copy._data, BYTES);
}

template <int32_t BYTES>
inline bool BitMask<BYTES>::operator==(const BitMask<BYTES>& mask) const
{
	return memcmp(_data, mask._data, BYTES) == 0;
}

#if 0
template <int32_t BYTES>
inline bool BitMask32<BYTES>::operator<(const BitMask<BYTES>& mask) const
{
	return _mask < mask._mask;
}

template <int32_t BYTES>
inline bool BitMask32<BYTES>::operator>(const BitMask<BYTES>& mask) const
{
	return _mask > mask._mask;
}

template <int32_t BYTES>
inline bool BitMask<BYTES>::operator<=(const BitMask<BYTES>& mask) const
{
	return operator<(mask) || this->operator==(mask);
}

template <int32_t BYTES>
inline bool BitMask<BYTES>::operator>=(const BitMask<BYTES>& mask) const
{
	return operator>(mask) || this->operator==(mask);
}
#endif

template <int32_t BYTES>
inline BitMask<BYTES>& BitMask<BYTES>::operator=(const BitMask<BYTES>& mask)
{
	memcpy(_data, mask._data, BYTES);
	return *this;
}

template <int32_t BYTES>
inline BitMask<BYTES>& BitMask<BYTES>::operator&=(const BitMask<BYTES>& mask)
{
	for (int32_t i = 0; i < BYTES; i++) {
		_data[i] &= mask._data[i];
	}
	return *this;
}

template <int32_t BYTES>
inline BitMask<BYTES>& BitMask<BYTES>::operator|=(const BitMask<BYTES>& mask)
{
	for (int32_t i = 0; i < BYTES; i++) {
		_data[i] |= mask._data[i];
	}
	return *this;
}

template <int32_t BYTES>
inline BitMask<BYTES>& BitMask<BYTES>::operator-=(const BitMask<BYTES>& mask)
{
	for (int32_t i = 0; i < BYTES; i++) {
		_data[i] &= ~mask._data[i];
	}
	return *this;
}

template <int32_t BYTES>
inline BitMask<BYTES>& BitMask<BYTES>::operator^=(const BitMask<BYTES>& mask)
{
	for (int32_t i = 0; i < BYTES; i++) {
		_data[i] ^= mask._data[i];
	}
	return *this;
}

template <int32_t BYTES>
inline BitMask<BYTES> operator&(const BitMask<BYTES>& m1, const BitMask<BYTES>& m2)
{
	BitMask<BYTES> result;
	for (int32_t i = 0; i < BYTES; i++) {
		result._data[i] = m1._data[i] & m2._data[i];
	}
	return result;
}

template <int32_t BYTES>
inline BitMask<BYTES> operator|(const BitMask<BYTES>& m1, const BitMask<BYTES>& m2)
{
	BitMask<BYTES> result;
	for (int32_t i = 0; i < BYTES; i++) {
		result._data[i] = m1._data[i] | m2._data[i];
	}
	return result;
}

template <int32_t BYTES>
inline BitMask<BYTES> operator-(const BitMask<BYTES>& m1, const BitMask<BYTES>& m2)
{
	BitMask<BYTES> result;
	for (int32_t i = 0; i < BYTES; i++) {
		result._data[i] = m1._data[i] & (~m2._data[i]);
	}
	return result;
}

template <int32_t BYTES>
inline BitMask<BYTES> operator^(const BitMask<BYTES>& m1, const BitMask<BYTES>& m2)
{
	BitMask<BYTES> result;
	for (int32_t i = 0; i < BYTES; i++) {
		result._data[i] = m1._data[i] ^ m2._data[i];
	}
	return result;
}

template <int32_t BYTES>
inline BitMask<BYTES> operator~(const BitMask<BYTES>& m1)
{
	BitMask<BYTES> result;
	for (int32_t i = 0; i < BYTES; i++) {
		result._data[i] = ~m1._data[i];
	}
	return result;
}

template <int32_t BYTES>
inline void BitMask<BYTES>::set(int32_t index)
{
	assert(0 <= index && index < BYTES * 8);
	_data[index / 8] |= 0x01 << (index % 8);
}

template <int32_t BYTES>
inline void BitMask<BYTES>::set(const BitMask<BYTES>& mask)
{
	for (int32_t i = 0; i < BYTES; i++) {
		_data[i] |= mask._data[i];
	}
}

template <int32_t BYTES>
inline void BitMask<BYTES>::reset(int32_t index)
{
	assert(0 <= index && index < BYTES * 8);
	_data[index / 8] &= ~(0x01 << (index % 8));
}

template <int32_t BYTES>
inline void BitMask<BYTES>::reset(const BitMask<BYTES>& mask)
{
	for (int32_t i = 0; i < BYTES; i++) {
		_data[i] &= ~mask._data[i];
	}
}

template <int32_t BYTES>
inline void BitMask<BYTES>::setAll()
{
	memset(_data, 0xFF, BYTES);
}

template <int32_t BYTES>
inline void BitMask<BYTES>::resetAll()
{
	memset(_data, 0, BYTES);
}

template <int32_t BYTES>
inline bool BitMask<BYTES>::isSet(int32_t index) const
{
	assert(0 <= index && index < BYTES * 8);
	return (_data[index / 8] & (0x01 << (index % 8))) != 0;
}

template <int32_t BYTES>
inline bool BitMask<BYTES>::isEmpty() const
{
	uint8_t zero[BYTES];
	memset(zero, 0, BYTES);
	return memcmp(zero, _data, BYTES) == 0;
}

template <int32_t BYTES>
inline bool BitMask<BYTES>::isSetAll() const
{
	uint8_t one[BYTES];
	memset(one, 0xFF, BYTES);
	return memcmp(one, _data, BYTES) == 0;
}

template <int32_t BYTES>
inline bool BitMask<BYTES>::isSetAll(const BitMask<BYTES>& mask) const
{
	return (*this & mask) == mask;
}

template <int32_t BYTES>
inline bool BitMask<BYTES>::isSetAny(const BitMask<BYTES>& mask) const
{
	return mask.isEmpty() || (*this & mask).isEmpty() == false;
}

template <int32_t BYTES>
inline uint8_t BitMask<BYTES>::asUint8() const
{
	return _data[0];
}

template <int32_t BYTES>
inline uint16_t BitMask<BYTES>::asUint16() const
{
	return (*(uint16_t*)_data);
}

template <int32_t BYTES>
inline uint32_t BitMask<BYTES>::asUint32() const
{
	return (*(uint32_t*)_data);
}

template <int32_t BYTES>
inline uint64_t BitMask<BYTES>::asUint64() const
{
	return (*(uint64_t*)_data);
}

template <int32_t BYTES>
const uint8_t* BitMask<BYTES>::asBytes() const
{
	return _data;
}

template <int32_t BYTES>
inline int32_t BitMask<BYTES>::setCount() const
{
	int32_t result = 0;

	for (int32_t i = 0; i < BYTES; i++) {
		uint32_t mask = 0x01;
		for (int32_t j = 0; j < 8; j++) {
			if (_data[i] & mask) {
				result++;
			}
			mask <<= 1;
		}		
	}

	return result;
}


typedef BitMask<1> BitMask8;
typedef BitMask<2> BitMask16;
typedef BitMask<8> BitMask64;

#endif	// __BIT_MASK_H__
